# $CoreSDI: Makefile.in,v 1.3.2.12.2.3.4.11 2001/11/20 09:56:24 alejo Exp $
#
# Copyright (c) 2001, Core SDI S.A., Argentina
# All rights reserved
# 
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
# 3. Neither name of the Core SDI S.A. nor the names of its contributors
#    may be used to endorse or promote products derived from this software
#    without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
# IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
# NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
# THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
# THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#

CC=		gcc
LD=		ld
CFLAGS=		-g -O2 -Wall
DCCFLAGS=	
CPPFLAGS=	-I.. 

INSTALL=	/usr/bin/ginstall -c
INSTALL_LIBDIR=	/usr/msyslog/lib/alat

MLIBNAME=	libmsyslog.so.1.08
MSRCS=		 im_linux.c im_udp.c om_udp.c im_unix.c im_file.c om_classic.c ttymsg.c om_tcp.c im_tcp.c ip_misc.c om_mysql.c om_pgsql.c sql_misc.c om_peo.c om_regex.c
MLIBS=		
MOBJS=		$(MSRCS:.c=.o)
HASH_SRCS=	../peo/hash.c ../peo/md5c.c ../peo/sha1.c ../peo/rmd160.c
HASH_OBJS=	hash.o md5c.o sha1.o rmd160.o

SHARED_PARAMS=	-Bshareable

$(MLIBNAME):  $(MSRCS) $(HASH_SRCS) ../config.h
	$(CC) $(CFLAGS) $(CPPFLAGS) $(DCCFLAGS) -c $(HASH_SRCS)
	$(CC) $(CFLAGS) $(CPPFLAGS) $(DCCFLAGS) -c $(MSRCS)
	$(LD) $(SHARED_PARAMS) -o $(MLIBNAME) $(MOBJS) $(HASH_OBJS)

all: $(MLIBNAME)

.PHONY:	clean distclean

clean:
	-rm -f core *.core *.o *.so.* *.so

distclean: clean
	-rm -f Makefile *.imp

install:
	@echo "installing shared library..."
	@if [ ! -d $(INSTALL_LIBDIR) ]; then \
		mkdir -p $(INSTALL_LIBDIR); \
	fi
	@$(INSTALL) $(MLIBNAME) $(INSTALL_LIBDIR)/ && echo "$(INSTALL_LIBDIR)/$(MLIBNAME)"

