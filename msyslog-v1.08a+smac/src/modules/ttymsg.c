/*	$OpenBSD: ttymsg.c,v 1.3 1996/10/25 06:06:30 downsj Exp $	*/
/*	$NetBSD: ttymsg.c,v 1.3 1994/11/17 07:17:55 jtc Exp $	*/

/*
 * Copyright (c) 1989, 1993
 *	The Regents of the University of California.  All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *	This product includes software developed by the University of
 *	California, Berkeley and its contributors.
 * 4. Neither the name of the University nor the names of its contributors
 *    may be used to endorse or promote products derived from this software
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
 * OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
 * SUCH DAMAGE.
 */


#include "config.h"

#include <sys/types.h>
#include <sys/uio.h>
#ifdef HAVE_DIRENT_H
# include <dirent.h>
#elif defined(HAVE_SYS_HNDIR_H)
# include <sys/ndir.h>
#elif defined(HAVE_SYS_HDIR_H)
# include <sys/dir.h>
#elif defined(HAVE_NDIR_H)
# include <ndir.h>
#else
# define MAXNAMLEN 128
# warning Using MAXNAMLEN of 128
#endif
#include <errno.h>
#include <fcntl.h>
#ifdef HAVE_PATHS_H
# include <paths.h>
#endif
#include <signal.h>
#include <stdio.h>
#include <string.h>
#include <unistd.h>
#include <strings.h>

#ifndef _PATH_DEV
#define _PATH_DEV "/dev/"
/* #warning Using "/dev/" for _PATH_DEV */
#endif

#ifndef sigsetmask
int sigsetmask(int);
#endif

/*
 * Display the contents of a uio structure on a terminal.  Used by wall(1),
 * syslogd(8), and talkd(8).  Forks and finishes in child if write would block,
 * waiting up to tmout seconds.  Returns pointer to error string on unexpected
 * error; string is not newline-terminated.  Various "normal" errors are
 * ignored (exclusive-use, lack of permission, etc.).
 */
char *
ttymsg(struct iovec *iov, int iovcnt, char *line, int tmout)
{
	static char device[MAXNAMLEN] = { _PATH_DEV };
	static char errbuf[1024];
	register int cnt, fd, left, wret;
	struct iovec localiov[6];
	int forked = 0;


	if (iovcnt > sizeof(localiov) / sizeof(localiov[0]))
		return ("too many iov's (change code in wall/ttymsg.c)");

	/*
	 * Ignore lines that start with "ftp" or "uucp".
	 */
	if ((strncmp(line, "ftp", 3) == 0)
	    || (strncmp(line, "uucp", 4) == 0))
		return (NULL);

	(void) strcpy(device + sizeof(_PATH_DEV) - 1, line);

#ifndef HAVE_LINUX
	if (strchr(device + sizeof(_PATH_DEV) - 1, '/')) {
		/* A slash is an attempt to break security... */
		(void) snprintf(errbuf, sizeof(errbuf), "'/' in \"%s\"",
		    device);
		return (errbuf);
	}
#endif

	/*
	 * open will fail on slip lines or exclusive-use lines
	 * if not running as root; not an error.
	 */
	if ((fd = open(device, O_WRONLY|O_NONBLOCK, 0)) < 0) {
		if (errno == EBUSY || errno == EACCES)
			return (NULL);
		(void) snprintf(errbuf, sizeof(errbuf),
		    "%s: %s", device, strerror(errno));
		return (errbuf);
	}

	for (cnt = left = 0; cnt < iovcnt; ++cnt)
		left += iov[cnt].iov_len;

	for (;;) {
		wret = writev(fd, iov, iovcnt);
		if (wret >= left)
			break;
		if (wret >= 0) {
			left -= wret;
			if (iov != localiov) {
				bcopy(iov, localiov,
				    iovcnt * sizeof(struct iovec));
				iov = localiov;
			}
			for (cnt = 0; wret >= iov->iov_len; ++cnt) {
				wret -= iov->iov_len;
				++iov;
				--iovcnt;
			}
			/* we assume writev() writes whole chunks.  posix? */
			continue;
		}
		if (errno == EWOULDBLOCK) {
			int cpid, off = 0;

			if (forked) {
				(void) close(fd);
				_exit(1);
			}
			cpid = fork();
			if (cpid < 0) {
				(void) snprintf(errbuf, sizeof(errbuf),
				    "fork: %s", strerror(errno));
				(void) close(fd);
				return (errbuf);
			}
			if (cpid) {	/* parent */
				(void) close(fd);
				return (NULL);
			}
			forked++;
			/* wait at most tmout seconds */
			(void) signal(SIGALRM, SIG_DFL);
			(void) signal(SIGTERM, SIG_DFL); /* XXX */
			sigsetmask(0);
			(void) alarm((u_int)tmout);
			(void) fcntl(fd, O_NONBLOCK, &off);
			continue;
		}
		/*
		 * We get ENODEV on a slip line if we're running as root,
		 * and EIO if the line just went away.
		 */
		if (errno == ENODEV || errno == EIO)
			break;
		(void) close(fd);
		if (forked)
			_exit(1);
		(void) snprintf(errbuf, sizeof(errbuf),
		    "%s: %s", device, strerror(errno));
		return (errbuf);
	}

	(void) close(fd);
	if (forked)
		_exit(0);
	return (NULL);
}
