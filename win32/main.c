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
#include "eventlog.h"
#include "log.h"
#include "syslog.h"
#include "getopt.h"

/* Main program */

/* Program variables */
static BOOL ProgramDebug = FALSE;
static BOOL ProgramInstall = FALSE;
static BOOL ProgramUninstall = FALSE;
static char * ProgramName;
static char * ProgramSyslogLogHost = NULL;
static char * ProgramSyslogPort = NULL;

/* Operate on program flags */
static int mainOperateFlags()
{
	int status = 0;

	/* Install new service */
	if (ProgramInstall) {

		/* Install registry */
		if (RegistryInstall())
			return 1;

		/* Install service */
		if (ServiceInstall())
			return 1;

		/* Success */
		return 0;
	}

	/* Uninstall service */
	if (ProgramUninstall) {

		/* Remove service */
		if (ServiceRemove())
			status = 1;

		/* Remove registry settings */
		if (RegistryUninstall())
			status = 1;

		/* Return status */
		return status;
	}

	/* Load the current registry keys */
	if (RegistryRead())
		return 1;

	/* Start network connection */
	if (SyslogOpen())
		return 1;

	/* If in debug mode, call main loop directly */
	if (ProgramDebug)
		status = MainLoop();
	else
		/* Otherwise, start service dispatcher, that will eventually call MainLoop */
		status = ServiceStart();

	/* Close syslog */
	SyslogClose();

	/* Return status */
	return status;
}

/* Program usage information */
static void mainUsage()
{
	if (LogInteractive) {
		fprintf(stderr, "Usage: %s -i|-u|-d [-h host] [-p port]\n", ProgramName);
		fputs("  -i           Install service\n", stderr);
		fputs("  -u           Uninstall service\n", stderr);
		fputs("  -d           Debug: run as console program\n", stderr);
		fputs("  -h host      Name of log host\n", stderr);
		fputs("  -p port      Port number of syslogd\n", stderr);
		fputc('\n', stderr);
		fprintf(stderr, "Default port: %u\n", SYSLOG_DEF_PORT);
		fputs("Host (-h) required if installing.\n", stderr);
		Sleep(10000);
	} else {
		Log(LOG_ERROR, "Invalid flag usage; Check startup parameters");
		Sleep(10000);
	}
}

/* Process flags */
static int mainProcessFlags(int argc, char ** argv)
{
	int flag;

	/* Note all actions */
	while ((flag = GetOpt(argc, argv, "iudh:p:")) != EOF) {
		switch (flag) {
		case 'i':
			ProgramInstall = TRUE;
			break;
		case 'u':
			ProgramUninstall = TRUE;
			break;
		case 'd':
			ProgramDebug = TRUE;
			break;
		case 'h':
			ProgramSyslogLogHost = GetOptArg;
			printf("%s\n", ProgramSyslogLogHost);
			break;
		case 'p':
			ProgramSyslogPort = GetOptArg;
			break;
		default:
			mainUsage();
			return 1;
		}
	}
	argc -= GetOptInd;
	argv += GetOptInd;
	if (argc) {
		mainUsage();
		return 1;
	}

	/* Must have only one of */
	if (ProgramInstall + ProgramUninstall + ProgramDebug > 1) {
		Log(LOG_ERROR, "Pass only one of -i, -u or -d");
		return 1;
	}

	/* If installing, must have a log host */
	if (ProgramInstall && ProgramSyslogLogHost == NULL) {
		Log(LOG_ERROR, "Syslogd host name (-h) flag required");
		return 1;
	}

	/* Check arguments */
	if (ProgramSyslogLogHost) {
		if (CheckSyslogLogHost(ProgramSyslogLogHost))
			return 1;
	}
	if (ProgramSyslogPort) {
		if (CheckSyslogPort(ProgramSyslogPort))
			return 1;
	}

	/* Proceed to do operation */
	return mainOperateFlags();
}

/* Main program */
int main(int argc, char ** argv)
{
	int status;

	/* Save program name */
	ProgramName = argv[0];

	/* Start eventlog */
	if (LogStart()) {
		return 1;
	} else {

		/* Start the network */
		if (WSockStart() == 0) {

			/* Process flags */
			status = mainProcessFlags(argc, argv);

			/* Stop network if needed */
			WSockStop();
		}
	}

	/* Show status */
	if (LogInteractive) {
		if (status)
			puts("Command did not complete due to a failure");
		else
			puts("Command completed successfully");
	}

	/* Stop event logging */
	LogStop();

	/* Success */
	return status;
}
