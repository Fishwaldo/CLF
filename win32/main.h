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

/* Basic include files */
#include <windows.h>
#include <winsock.h>
#include <lm.h>
#include <stdio.h>
#include <string.h>
#include <time.h>

/* Macros */
#define COUNT_OF(x)	(sizeof(x)/sizeof(*x))

/* Prototypes */
int CheckSyslogFacility(char * facility);
int CheckSyslogPort(char * port);
int CheckSyslogLogHost(char * loghost);
BOOL WINAPI DllMain(HINSTANCE hinstDLL, DWORD fdwReason, LPVOID lpvReserved);
int EventlogCreate(char * name);
void EventlogsClose(void);
int EventlogsOpen(void);
char * EventlogNext(int log, int * level);
int GetOpt(int nargc, char ** nargv, char * ostr);
int LogStart(void);
void LogStop(void);
void Log(int level, char * message, ...);
int MainLoop(void);
int main(int argc, char ** argv);
int RegistryInstall(void);
int RegistryUninstall(void);
int RegistryRead(void);
int RegistryGather(void);
int ServiceInstall(void);
int ServiceRemove(void);
DWORD WINAPI ServiceStart(void);
void GetError(DWORD err_num, char * message, int len);
char * GetUsername(SID * sid);
char * LookupMessageFile(char * logtype, char * source);
char * FormatLibraryMessage(char * message_file, DWORD event_id, char ** string_array);
int SyslogOpen(void);
void SyslogClose(void);
int SyslogSend(char * message, int level);
int WSockStart(void);
void WSockStop(void);
int WSockOpen(unsigned long ip, unsigned short port);
void WSockClose(void);
int WSockSend(char * message);
