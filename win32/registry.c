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
#include "eventlog.h"

/* Registry */

/* List of registry items to store */
struct RegistryData {
	char * name;			/* Name of key			*/
	DWORD key_type;			/* Type of key			*/
	void * value;			/* Value of key			*/
	DWORD size;			/* Size of key			*/
	int required;			/* Key is required		*/
};

/* Location of application data in registry tree */
static char RegistryApplicationDataPath[] = "Software\\CSC\\CLFAgent\\1.1";

/* List of application data */
static struct RegistryData RegistryApplicationDataList[] = {
	{ "LoghostIP", REG_DWORD, &SyslogLogHostIP, sizeof(SyslogLogHostIP), TRUE },
	{ "Port", REG_DWORD, &SyslogPort, sizeof(SyslogPort), TRUE }
};

/* Location of eventlog data in registry tree */
static char RegistryEventlogDataPath[] = "System\\CurrentControlSet\\Services\\EventLog\\Application\\CLFAgent";

/* List of eventlog data */
static char RegistryEventlogFile[] = "%SystemRoot%\\System32\\CLFAgent.dll";
static DWORD RegistryEventlogTypes = EVENTLOG_ERROR_TYPE | EVENTLOG_WARNING_TYPE | EVENTLOG_INFORMATION_TYPE;

static struct RegistryData RegistrEventlogDataList[] = {
	{ "EventMessageFile", REG_EXPAND_SZ, RegistryEventlogFile, sizeof(RegistryEventlogFile)-1 },
	{ "TypesSupported", REG_DWORD, &RegistryEventlogTypes, sizeof(RegistryEventlogTypes) }
};

/* Location of eventlog keys */
static char RegistryEventlogKeyPath[] = "SYSTEM\\CurrentControlSet\\Services\\Eventlog";

/* Create default entries into registry */
static int RegistryCreate(char * path, struct RegistryData * list, int count)
{
	HKEY registry_handle;
	DWORD disposition;
	int i;

	/* Open location for putting key information */
	if (RegCreateKeyEx(HKEY_LOCAL_MACHINE,
		path,
		0,
		"",
		REG_OPTION_NON_VOLATILE,
		KEY_ALL_ACCESS,
		NULL,
		&registry_handle,
		&disposition)) {
		Log(LOG_ERROR|LOG_SYS, "Cannot initialize access to registry: \"%s\" %d", path, WSAGetLastError());
		return 1;
	}
	
	/* Check for existing */
	if (disposition == REG_OPENED_EXISTING_KEY)
		Log(LOG_WARNING, "Replacing existing keys: \"%s\"", path);

	/* Process all values */
	for (i = 0; i < count; i++) {

		/* Set value */
		if (RegSetValueEx(registry_handle,
			list[i].name,
			0,
			list[i].key_type,
			list[i].value,
			list[i].size)) {
			Log(LOG_ERROR|LOG_SYS, "Cannot install registry key: Key=\"%s\"", list[i].name);
			break;
		}
	}

	/* Close registry */
	RegCloseKey(registry_handle);

	/* Return status */
	return i != count;
}

/* Delete registry entries */
static int RegistryDelete(char * path)
{
	/* Open location for putting key information */
	if (RegDeleteKey(HKEY_LOCAL_MACHINE, path)) {
		Log(LOG_ERROR|LOG_SYS, "Cannot delete registry keys: \"%s\"", path);
		return 1;
	}
	return 0;
}

/* Install registry settings */
int RegistryInstall()
{
	/* Register application and eventlog data */
	if (RegistryCreate(RegistryApplicationDataPath, RegistryApplicationDataList, COUNT_OF(RegistryApplicationDataList)))
		return 1;
	if (RegistryCreate(RegistryEventlogDataPath, RegistrEventlogDataList, COUNT_OF(RegistrEventlogDataList)))
		return 1;

	/* Success */
	return 0;
}

/* Remove registry settings */
int RegistryUninstall()
{
	int status = 0;

	/* Remove application and eventlog data */
	if (RegistryDelete(RegistryApplicationDataPath))
		status = 1;
	if (RegistryDelete(RegistryEventlogDataPath))
		status = 1;

	/* Return overall status */
	return status;
}

/* Read registry settings */
int RegistryRead()
{
	DWORD key_type;
	HKEY registry_handle;
	int e;
	int i;

	/* Open location for putting key information */
	if (RegOpenKey(HKEY_LOCAL_MACHINE, RegistryApplicationDataPath, &registry_handle)) {
		Log(LOG_ERROR|LOG_SYS, "Cannot initialize access to registry: \"%s\"", RegistryApplicationDataPath);
		return 1;
	}

	/* Process all values */
	for (i = 0; i < COUNT_OF(RegistryApplicationDataList); i++) {

		/* Get value */
		if (e = RegQueryValueEx(registry_handle, RegistryApplicationDataList[i].name, 0, &key_type, RegistryApplicationDataList[i].value, &RegistryApplicationDataList[i].size))
			if (e != ERROR_FILE_NOT_FOUND || RegistryApplicationDataList[i].required) {
				Log(LOG_ERROR|LOG_SYS, "Cannot query registry key: \"%s\"", RegistryApplicationDataList[i].name);
				break;
			}
	}

	/* Close registry */
	RegCloseKey(registry_handle);

	/* Return status */
	return i != COUNT_OF(RegistryApplicationDataList);
}

/* Gather list of keys */
int RegistryGather()
{
	DWORD size;
	HKEY registry_handle;
	char name[EVENTLOG_NAME_SZ];
	int errnum;
	int index;

	/* Open location for enumerating key information */
	if (RegOpenKey(HKEY_LOCAL_MACHINE, RegistryEventlogKeyPath, &registry_handle)) {
		Log(LOG_ERROR|LOG_SYS, "Cannot initialize access to registry: \"%s\"", RegistryEventlogKeyPath);
		return 1;
	}

	/* Process keys until end of list */
	index = 0;
	while (1) {

		/* Get next key */
		size = sizeof(name);
		errnum = RegEnumKey(registry_handle, index, name, size);

		/* Stop if last item */
		if (errnum == ERROR_NO_MORE_ITEMS)
			break;

		/* Check for error */
		if (errnum) {
			Log(LOG_ERROR|LOG_SYS, "Cannot enumerate registry key: \"%s\"", RegistryEventlogKeyPath);
			break;
		}

		/* Create new eventlog */
		if (EventlogCreate(name))
			break;

		/* Advance index number */
		index++;
	}

	/* Close registry */
	RegCloseKey(registry_handle);

	/* Return status */
	return errnum != ERROR_NO_MORE_ITEMS;
}
