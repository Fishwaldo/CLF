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

/* Number of eventlogs */
#define EVENTLOG_SZ		32

/* Size of buffer */
#define EVENTLOG_BUF_SZ		(10*1024)

/* Number of strings in formatted message */
#define EVENTLOG_ARRAY_SZ	96

/* Eventlog descriptor */
struct Eventlog {
	char name[EVENTLOG_NAME_SZ];	/* Name of eventlog		*/
	HANDLE handle;			/* Handle to eventlog		*/
	char buffer[EVENTLOG_BUF_SZ];	/* Message buffer		*/
	int count;			/* Number of messages left	*/
	int pos;			/* Current position in buffer	*/
	int recnum;			/* Next record number		*/
};

/* List of eventlogs */
static struct Eventlog EventlogList[EVENTLOG_SZ];
int EventlogCount = 0;

/* Create new eventlog descriptor */
int EventlogCreate(char * name)
{
	/* Check count */
	if (EventlogCount == EVENTLOG_SZ) {
		Log(LOG_ERROR, "Too many eventlogs: %u", EVENTLOG_SZ);
		return 1;
	}

	/* Store new name */
	strcpy(EventlogList[EventlogCount].name, name);

	/* Increament count */
	EventlogCount++;

	/* Success */
	return 0;
}

/* Close eventlog */
static void EventlogClose(int log)
{
	/* Close log */
	CloseEventLog(EventlogList[log].handle);
	EventlogList[log].handle = NULL;
}

/* Close eventlogs */
void EventlogsClose()
{
	int i;

	/* Loop until list depleated */
	for (i = 0; i < EventlogCount; i++)
		if (EventlogList[i].handle)
			EventlogClose(i);

	/* Reset count */
	EventlogCount = 0;
}

/* Open event log */
static int EventlogOpen(int log)
{
	DWORD count;
	DWORD oldest;

	/* Reset all indicators */
	EventlogList[log].count = 0;
	EventlogList[log].pos = 0;
	EventlogList[log].recnum = 1;

	/* Open log */
	EventlogList[log].handle = OpenEventLog(NULL, EventlogList[log].name);
	if (EventlogList[log].handle == NULL) {
		Log(LOG_ERROR|LOG_SYS, "Cannot open event log: \"%s\"", EventlogList[log].name);
		return 1;
	}

	/* Get oldest record number */
	if (GetOldestEventLogRecord(EventlogList[log].handle, &oldest) == 0) {
		Log(LOG_ERROR|LOG_SYS, "Cannot get oldest record number for event log: \"%s\"", EventlogList[log].name);
		return 1;
	}

	/* Get number of records to skip */
	if (GetNumberOfEventLogRecords(EventlogList[log].handle, &count) == 0) {
		Log(LOG_ERROR|LOG_SYS, "Cannot get record count for event log: \"%s\"", EventlogList[log].name);
		return 1;
	}

	/* Store record of next event */
	EventlogList[log].recnum = oldest + count;
	if (EventlogList[log].recnum == 0)
		EventlogList[log].recnum = 1; /* ?? */

	/* Success */
	return 0;
}

/* Open event logs */
int EventlogsOpen()
{
	int i;

	/* Open the log files */
	for (i = 0; i < EventlogCount; i++)
		if (EventlogOpen(i))
			break;

	/* Check for errors */
	if (i != EventlogCount) {
		EventlogsClose();
		return 1;
	}

	/* Success */
	return 0;
}

/* Get the next eventlog message */
char * EventlogNext(int log, int * level)
{
	BOOL reopen = FALSE;
	DWORD errnum;
	DWORD needed;
	EVENTLOGRECORD * event;
	char * cp;
	char * current;
	char * formatted_string;
	char * message_file;
	char * source;
	char * string_array[EVENTLOG_ARRAY_SZ];
	char * username;
	int i;

	static char message[SYSLOG_SZ+1];

	/* Are there any records left in buffer */
	while (EventlogList[log].pos == EventlogList[log].count) {

		/* Reset input position */
		EventlogList[log].count = 0;
		EventlogList[log].pos = 0;

		/* Read a record */
		needed = 0;
		if (ReadEventLog(EventlogList[log].handle, EVENTLOG_FORWARDS_READ | EVENTLOG_SEEK_READ, EventlogList[log].recnum, EventlogList[log].buffer, sizeof(EventlogList[log].buffer), &EventlogList[log].count, &needed) == 0) {

			/* Check error */
			errnum = GetLastError();
			switch (errnum) {

			/* Message too large... skip over */
			case ERROR_INSUFFICIENT_BUFFER:
				Log(LOG_WARNING, "Eventlog message size too large: \"%s\": %u bytes", EventlogList[log].name, needed);
				EventlogList[log].recnum++;
				break;

			/* Eventlog corrupted (?)... Reopen */
			case ERROR_EVENTLOG_FILE_CORRUPT:
				Log(LOG_INFO, "Eventlog was corrupted: \"%s\"", EventlogList[log].name);
				reopen = TRUE;
				break;

			/* Eventlog files are clearing... Reopen */
			case ERROR_EVENTLOG_FILE_CHANGED:
				Log(LOG_INFO, "Eventlog was cleared: \"%s\"", EventlogList[log].name);
				reopen = TRUE;
				break;

			/* Record not available (yet) */
			case ERROR_INVALID_PARAMETER:
				return NULL;

			/* Normal end of eventlog messages */
			case ERROR_HANDLE_EOF:
				return NULL;

			/* Eventlog probably closing down */
			case RPC_S_UNKNOWN_IF:
				return NULL;

			/* Unknown condition */
			default:
				Log(LOG_ERROR|LOG_SYS, "Eventlog returned error: \"%s\"", EventlogList[log].name);
				ServiceIsRunning = FALSE;
				return NULL;
			}

			/* Process reopen */
			if (reopen) {
				EventlogClose(log);
				if (EventlogOpen(log)) {
					ServiceIsRunning = FALSE;
					return NULL;
				}
				reopen = FALSE;
			}
		}
	}

	/* Increase record number */
	EventlogList[log].recnum++;

	/* Get position into buffer */
	current = EventlogList[log].buffer + EventlogList[log].pos;

	/* Get pointer to current event record */
	event = (EVENTLOGRECORD *) current;

	/* Advance position */
	EventlogList[log].pos += event->Length;

	/* Check number of strings */
	if (event->NumStrings > COUNT_OF(string_array)) {
		Log(LOG_WARNING, "Eventlog message has too many strings to print message: \"%s\": %u strings", EventlogList[log].name, event->NumStrings);
		return NULL;
	}

	/* Convert strings to arrays */
	cp = current + event->StringOffset;
	for (i = 0; i < event->NumStrings; i++) {
		string_array[i] = cp;
		while (*cp++ != '\0');
	}

	/* Get source */
	source = current + sizeof(*event);

	/* Select syslog level */
	switch (event->EventType) {

	case EVENTLOG_ERROR_TYPE:
		*level = SYSLOG_BUILD(SYSLOG_DAEMON, SYSLOG_ERR);
		break;
	case EVENTLOG_WARNING_TYPE:
		*level = SYSLOG_BUILD(SYSLOG_DAEMON, SYSLOG_WARNING);
		break;
	case EVENTLOG_INFORMATION_TYPE:
		*level = SYSLOG_BUILD(SYSLOG_DAEMON, SYSLOG_NOTICE);
		break;
	case EVENTLOG_AUDIT_SUCCESS:
		*level = SYSLOG_BUILD(SYSLOG_DAEMON, SYSLOG_NOTICE);
		break;
	case EVENTLOG_AUDIT_FAILURE:
		*level = SYSLOG_BUILD(SYSLOG_DAEMON, SYSLOG_ERR);
		break;

	/* Everything else */
	case EVENTLOG_SUCCESS:
	default:
		*level = SYSLOG_BUILD(SYSLOG_DAEMON, SYSLOG_NOTICE);
		break;
	}

	/* Convert user */
	if (event->UserSidLength > 0)
		username = GetUsername((SID *) (current + event->UserSidOffset));
	else
		username = "N/A";

	/* Get resource */
	message_file = LookupMessageFile(EventlogList[log].name, source);
	if (message_file == NULL)
		return NULL;
		
	/* Format eventlog message */
	formatted_string = FormatLibraryMessage(message_file, event->EventID, string_array);
	if (formatted_string == NULL)
		return NULL;

	/* Output message */
	_snprintf(message, sizeof(message), "%s: %s: %s",
		source,
		username,
		formatted_string);
	return message;
}
