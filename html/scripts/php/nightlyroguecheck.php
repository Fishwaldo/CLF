#!/usr/bin/php -q
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
	$REMOTE_ID=sec_usernametoid($sec_dbsocket,'msyslog');
	$GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog msyslog');
	if ( ! sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) {
		dbdisconnect($sec_dbsocket);
		exit;
	}
	$dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);

	$month=date("M",(time()-86400));
	$day=date("d",(time()-86400));
	$year=date("Y",(time()-86400));

	$date="$month-$day-$year";

	$SQLQuery="select distinct host from TSyslog where date >= '$date' except select THost_Host as host from Syslog_THost union select distinct host from Syslog_TArchive where date >= '$date' except select THost_Host as host from Syslog_THost";

	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $SQLNumRows ) {
		$hosttext="The following hosts are logging to SMT but are not defined:\n\r";
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$hosttext=$hosttext . stripslashes(pgdatatrim($SQLQueryResultsObject->host)) . "\n\r" ;
		}
		echo $hosttext;
		mail(WARNINGADDRESS,"SMT Rogue Warning",$hosttext);
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");

	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
%>
