#!/usr/bin/php
<%
/*=============================================================================
 *
 * Copyright 2004 Jeremy Guthrie  smt@dangermen.com
 *
 * This is free software; you can redistribute it and/or modify
 * it under the terms of version 2 only of the GNU General Public License as
 * published by the Free Software Foundation.
 *
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA
 *
=============================================================================*/

/*
CREATE TABLE SecFrame_TQueue (
  TQueue_ID integer DEFAULT nextval('TQueue_Seq'),
  TQueue_Command varchar(16) NOT NULL,
  TQueue_Date date NOT NULL,
  TQueue_Time time NOT NULL,
  TQueue_DateProcessed date,
  TQueue_TimeProcessed time,
  TQueue_Processed integer,
  TQueue_Data1 text,
  TQueue_Data2 text
)\g
*/
	require_once('/opt/apache/htdocs/login/lib/pgsql.php');
	require_once('/opt/apache/htdocs/login/lib/generalweb.php');
	require_once('/opt/apache/htdocs/login/lib/secframe.php');

	$sec_dbsocket=sec_dbconnect();

	$date=date("M-d-Y",(time() - 86400));

	$SQLQuery="select TSyslog.TSyslog_ID,TSyslog.host,TSyslog.date,TSyslog.time,TSyslog.message,TSyslog.Facility,TSyslog.Severity" ;

	$SQLQueryResults = pg_exec($sec_dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $SQLNumRows != 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tsyslog_id));

			$results=shell_exec($command);
			$date=stripslashes(pgdatatrim($SQLQueryResultsObject->date));
			$time=stripslashes(pgdatatrim($SQLQueryResultsObject->time));
			$host=stripslashes(pgdatatrim($SQLQueryResultsObject->host));
			$message=stripslashes(pgdatatrim($SQLQueryResultsObject->message));
			$vseverity=verboseseverity(stripslashes(pgdatatrim($SQLQueryResultsObject->severity)));
			$vfacility=verbosefacility(stripslashes(pgdatatrim($SQLQueryResultsObject->facility)));
		}
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");

	dbdisconnect($sec_dbsocket);
%>
