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

/* Number of libaries to load */
#define LIBRARY_SZ		4

/* Get error message */
void GetError(DWORD err_num, char * message, int len)
{
	/* Attempt to get the message text */
	if (FormatMessage(FORMAT_MESSAGE_FROM_SYSTEM | FORMAT_MESSAGE_IGNORE_INSERTS, NULL, err_num, MAKELANGID(LANG_NEUTRAL, SUBLANG_DEFAULT), message, len, NULL) == 0)
		_snprintf(message, len, "(Error %u)", err_num);
}

/* Get user name string for a SID */
char * GetUsername(SID * sid)
{
	DWORD name_len;
	DWORD domain_len;
	SID_NAME_USE snu;
	char name[UNLEN+1];
	char domain[DNLEN+1];
	int c;

	static char result[256];

	/* Initialize lengths for return buffers */
	name_len = sizeof(name);
	domain_len = sizeof(domain);

	/* Convert SID to name and domain */
	if (LookupAccountSid(NULL, sid, name, &name_len, domain, &domain_len, &snu) == 0) {

		/* Could not convert - make printable version of numbers */
		_snprintf(result, sizeof(result), "S-%u", sid->Revision);
		for (c = 0; c < COUNT_OF(sid->IdentifierAuthority.Value); c++)
			if (sid->IdentifierAuthority.Value[c])
				_snprintf(result, sizeof(result), "%s-%u", result, sid->IdentifierAuthority.Value[c]);
		for (c = 0; c < sid->SubAuthorityCount; c++)
			_snprintf(result, sizeof(result), "%s-%u", result, sid->SubAuthority[c]);

		Log(LOG_ERROR|LOG_SYS, "Cannot find SID for '%s'", result);
	} else
		_snprintf(result, sizeof(result), "%s\\%s", domain, name);

	/* Result result */
	return result;
}

/* Look up message file key */
char * LookupMessageFile(char * logtype, char * source)
{
	HKEY hkey;
	DWORD key_size;
	DWORD key_type;
	LONG status;
	char key[256];
	char key_value[256];

	static char result[256];

	/* Get key to registry */
	_snprintf(key, sizeof(key), "SYSTEM\\CurrentControlSet\\Services\\Eventlog\\%s\\%s", logtype, source);
	if (RegOpenKeyEx(HKEY_LOCAL_MACHINE, key, 0, KEY_READ, &hkey) != ERROR_SUCCESS) {
		Log(LOG_ERROR|LOG_SYS, "Cannot find key: \"%s\"", key);
		return NULL;
	}

	/* Get message file from registry */
	key_size = sizeof(key_value);
	status = RegQueryValueEx(hkey, "EventMessageFile", NULL, &key_type, key_value, &key_size);
	RegCloseKey(hkey);
	if (status != ERROR_SUCCESS) {
		Log(LOG_ERROR|LOG_SYS, "Cannot find key value \"EventMessageFile\": \"%s\"", key);
		return NULL;
	}

	/* Expand any environmental strings */
	if (key_type == REG_EXPAND_SZ) {
		if (ExpandEnvironmentStrings(key_value, result, sizeof(result)) == 0) {
			Log(LOG_ERROR|LOG_SYS, "Cannot expand string: \"%s\"", key_value);
			return NULL;
		}
	} else
		strcpy(result, key_value);
	return result;
}

/* Format library message */
char * FormatLibraryMessage(char * message_file, DWORD event_id, char ** string_array)
{
	BOOL is_space = FALSE;
	DWORD error_code;
	HINSTANCE library[LIBRARY_SZ];
	HRESULT status;
	char * ip;
	char * op;
	char * result = NULL;
	char ch;
	char exp_ip[256];
	int count = 0;
	int i;

	static char message_buffer[65535];
	static char message_text[65535];

	/* Process each file, seperated by semicolons */
	ip = message_file;
	do {
		/* Check library count */
		if (count == COUNT_OF(library)) {
			Log(LOG_ERROR|LOG_SYS, "Too many message files: %u files", LIBRARY_SZ);
			goto error;
		}

		/* Seperate paths */
		op = strchr(ip, ';');
		if (op)
			*op++ = '\0';

		/* Does this require ExpandEnvironmentStrings() to be called? */
		if (ExpandEnvironmentStrings(ip, exp_ip, sizeof(exp_ip)) == 0) {
			Log(LOG_ERROR|LOG_SYS, "Cannot expand string: \"%s\"", ip);
			goto error;
		}

		/* Load library */
		library[count] = LoadLibraryEx(exp_ip, NULL, LOAD_LIBRARY_AS_DATAFILE);
		if (library[count] == NULL) {
			Log(LOG_ERROR|LOG_SYS, "Cannot load message file: \"%s\"", exp_ip);
			goto error;
		}
		count++;

		/* Go to next library */
		ip = op;
	} while (ip);

	/* Format message */
	status = FormatMessage(FORMAT_MESSAGE_FROM_HMODULE|FORMAT_MESSAGE_ARGUMENT_ARRAY, library[0], event_id, MAKELANGID(LANG_NEUTRAL, SUBLANG_DEFAULT), message_text, sizeof(message_text), string_array);

	/* Check status */
	if (status == 0) {

		/* Check last error */
		status = GetLastError();

		/* Is this a message file missing problem? */
		if (HRESULT_CODE(status) == ERROR_MR_MID_NOT_FOUND)

			/* Reformat message */
			_snprintf(message_text, sizeof(message_text), "Unknown Event: %u, Facility: %u, Status: %s, Message file(s): %s", HRESULT_CODE(event_id), HRESULT_FACILITY(event_id), FAILED(event_id) ? "Failure" : "Success", message_file);

		else {
			/* Unknown error */
			Log(LOG_ERROR|LOG_SYS, "Cannot log event ID %u", event_id);
			goto error;
		}
	}

	/* Collapse white space */
	ip = message_text;
	op = message_text;
	while ((ch = *ip++) != '\0') {
		if (ch == '\r' || ch == '\t' || ch == '\n')
			ch = ' ';
		if (ch == ' ') {
			if (is_space)
				continue;
			is_space = TRUE;
		} else
			is_space = FALSE;
		*op++ = ch;
	}
	*op = '\0';

	/* Expand any double % */
	ip = message_text;
	op = message_buffer;
#define ep (message_buffer + sizeof(message_buffer) - 1)
	while ((ch = *ip++) != '\0') {

		/* Stop if buffer full */
		if (op >= ep)
			break;

		/* Did we find a "%%" */
		if (ch == '%' && *ip == '%') {

			/* Convert to error number */
			error_code = strtol(++ip, &ip, 10);

			/* Insert error message over input */
			GetError(error_code, op, ep - op);

			/* Update output position */
			op = message_buffer + strlen(message_buffer);
		} else

			/* Add character to output */
			*op++ = ch;
	}
	*op = '\0';

	/* Success */
	result = message_buffer;

error:
	/* Close libraries */
	for (i = 0; i < count; i++)
		FreeLibrary(library[i]);

	/* Return result */
	return result;
}

