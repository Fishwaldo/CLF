#!/opt/bin/php
<%
/*=============================================================================
 * $Id$
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

	require_once('../../config.php');

	$sec_dbsocket=sec_dbconnect();
	$REMOTE_ID=sec_usernametoid($sec_dbsocket,$REMOTE_USER);
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog msyslog'); 
        if ( ! sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) {
                dbdisconnect($sec_dbsocket);
                exit;
        }
        $dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);

	$date=date("M-d-Y",(time() - 86400));

	$SQLQuery="select TSyslog.TSyslog_ID,TSyslog.host,TSyslog.date,TSyslog.time,TSyslog.message,TSyslog.Facility,TSyslog.Severity" .
		" from TSyslog,Syslog_TProcess,Syslog_TProcessorProfile where ( " .
		" ( Syslog_TProcess.TProcess_Host=TSyslog.host )" .
		" and ( Syslog_TProcessorProfile.TLogin_ID=$REMOTE_ID ) and " .
		" ( TSyslog.host=Syslog_TProcessorProfile.TProcessorProfile_Host) and ( TSyslog.date = '$date' ) ) order by host,date,time,TSyslog_ID";

	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $SQLNumRows != 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tsyslog_id));
			$date=stripslashes(pgdatatrim($SQLQueryResultsObject->date));
			$time=stripslashes(pgdatatrim($SQLQueryResultsObject->time));
			$host=stripslashes(pgdatatrim($SQLQueryResultsObject->host));
			$message=stripslashes(pgdatatrim($SQLQueryResultsObject->message));
			$vseverity=verboseseverity(stripslashes(pgdatatrim($SQLQueryResultsObject->severity)));
			$vfacility=verbosefacility(stripslashes(pgdatatrim($SQLQueryResultsObject->facility)));
			echo "$date $time $host $vfacility $vseverity $message\n";
		}
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");

	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
%>
