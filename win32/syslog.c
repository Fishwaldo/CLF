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
#include "syslog.h"

/* syslog */

/* Application data configuration */
DWORD SyslogLogHostIP;
DWORD SyslogPort = SYSLOG_DEF_PORT;

/* Open syslog connection */
int SyslogOpen()
{
	return WSockOpen(SyslogLogHostIP, (unsigned short) SyslogPort);
}

/* Close syslog connection */
void SyslogClose()
{
	WSockClose();
}

/* Send a message to the syslog server */
int SyslogSend(char * message, int level)
{
	char error_message[SYSLOG_SZ];

	/* Write priority level */
	_snprintf(error_message, sizeof(error_message), "<%d>%s", level, message);

	/* Send result to syslog server */
	return WSockSend(error_message);
}
