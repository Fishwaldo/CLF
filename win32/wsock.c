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

/* WinSock */

/* Indicate if WSAStartup was called */
static BOOL WSockStarted = FALSE;

/* Connection socket */
SOCKET WSockSocket = INVALID_SOCKET;

/* Where to send syslog information */
static struct sockaddr_in WSockAddress;

/* Start Winsock access */
int WSockStart()
{
	WSADATA ws_data;

	/* Check to see if started */
	if (WSockStarted == FALSE) {

		/* See if version 2.0 is available */
		if (WSAStartup(MAKEWORD(2, 0), &ws_data)) {
			Log(LOG_ERROR, "Cannot initialize WinSock interface");
			return 1;
		}

		/* Set indicator */
		WSockStarted = TRUE;
	}

	/* Success */
	return 0;
}

/* Stop Winsock access */
void WSockStop()
{
	/* Check to see if started */
	if (WSockStarted) {

		/* Clean up winsock interface */
		WSACleanup();

		/* Reset indicator */
		WSockStarted = FALSE;
	}
}

/* Open connection to syslog */
int WSockOpen(unsigned long ip, unsigned short port)
{
	int ret;
	/* Initialize remote address structure */
	WSockAddress.sin_family = AF_INET;
	WSockAddress.sin_port = htons(port);
	WSockAddress.sin_addr.s_addr = ip;

	/* Create socket */
	WSockSocket = socket(AF_INET, SOCK_STREAM, 0);
	if (WSockSocket == INVALID_SOCKET) {
		Log(LOG_ERROR|LOG_SYS, "Cannot create a datagram socket");
		return 1;
	}
	ret = connect (WSockSocket, (struct sockaddr *) &WSockAddress, sizeof (WSockAddress));
	if (ret<0) {
		Log(LOG_ERROR|LOG_SYS, "Winsock Error: %d", WSAGetLastError());
		WSockClose();
		return 1;
	}
	/* Success */
	return 0;
}

/* Close connection */
void WSockClose()
{
	/* Close if open */
	if (WSockSocket != INVALID_SOCKET) {
		closesocket(WSockSocket);
		WSockSocket = INVALID_SOCKET;
	}
}

/* Send data to syslog */
int WSockSend(char * message)
{
	size_t len;

	/* Get message length */
	len = strlen(message);

	/* Send to syslog server */
	if (sendto(WSockSocket, message, len, 0, (struct sockaddr *) &WSockAddress, sizeof(WSockAddress)) != len) {
		if (h_errno != WSAEHOSTUNREACH && h_errno != WSAENETUNREACH) {
			Log(LOG_ERROR|LOG_SYS, "Cannot send message through socket");
			return 1;
		}
	}

	/* Success */
	return 0;
}
