/*
  Copyright (c) 1998-2003, Purdue University
  All rights reserved.

  Redistribution and use in source and binary forms are permitted provided
  that:

  (1) source distributions retain this entire copyright notice and comment,
      and
  (2) distributions including binaries display the following acknowledgement:

         "This product includes software developed by Purdue University."

      in the documentation or other materials provided with the distribution
      and in all advertising materials mentioning features or use of this
      software.

  The name of the University may not be used to endorse or promote products
  derived from this software without specific prior written permission.

  THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT ANY EXPRESS OR IMPLIED
  WARRANTIES, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF
  MERCHANTABILITY AND/OR FITNESS FOR A PARTICULAR PURPOSE.

  This software was developed by:
     Curtis Smith

     Purdue University
     Engineering Computer Network
     465 Northwestern Avenue
     West Lafayette, Indiana 47907-2035 U.S.A.

  Send all comments, suggestions, or bug reports to:
     software@ecn.purdue.edu
*/

/* Include files */
#include "main.h"
#include "log.h"
#include "syslog.h"

/* Check port number */
int CheckSyslogPort(char * port)
{
	char * eos;
	int value;
	struct servent * service;

	/* Try converting to integer */
	value = strtol(port, &eos, 10);
	if (eos == port || *eos != '\0') {

		/* Try looking up name */
		service = getservbyname(port, "udp");
		if (service == NULL) {
			Log(LOG_ERROR, "Invalid service name: \"%s\"", port);
			return 1;
		}

		/* Convert back to host order */
		value = ntohs(service->s_port);
	} else {

		/* Check for valid number */
		if (value <= 0 || value > 0xffff) {
			Log(LOG_ERROR, "Invalid service number: %u", value);
			return 1;
		}
	}

	/* Store new value */
	SyslogPort = value;

	/* Success */
	return 0;
}

/* Check log host */
int CheckSyslogLogHost(char * loghost)
{
	unsigned long ip;
	struct hostent * host;

	/* Attempt to convert IP number */
	ip = inet_addr(loghost);
	if (ip == -1) {

		/* Attempt to convert host name */
		host = gethostbyname(loghost);
		if (host == NULL) {
			Log(LOG_ERROR, "Invalid log host: \"%s\"", loghost);
			return 1;
		}

		/* Set ip */
		ip = *(unsigned long *)host->h_addr;
	}

	/* Store new value */
	SyslogLogHostIP = ip;

	/* Success */
	return 0;
}
