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
#include "service.h"
#include "syslog.h"

/* Main eventlog monitoring loop */
int MainLoop()
{
	char * output;
	int log;
	int level;

	/* Gather eventlog names */
	if (RegistryGather())
		return 1;

	/* Open all eventlogs */
	if (EventlogsOpen())
		return 1;

	/* Service is now running */
	Log(LOG_INFO, "Eventlog to Syslog Service Started: Version 3.4");

	/* Loop while service is running */
	do {

		/* Process records */
		for (log = 0; log < EventlogCount; log++) {

			/* Loop for all messages */
			while ((output = EventlogNext(log, &level)))
				if (SyslogSend(output, level)) {
					ServiceIsRunning = FALSE;
					break;
				}
		}

		/* Sleep five seconds */
		Sleep(5000);

	} while (ServiceIsRunning);

	/* Service is stopped */
	Log(LOG_INFO, "Eventlog to Syslog Service Stopped");

	/* Close eventlogs */
	EventlogsClose();

	/* Success */
	return 0;
}
