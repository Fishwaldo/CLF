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
	$APP_ID=sec_appnametoid($sec_dbsocket,'SyslogOp');
	if ( ! sec_accessallowed($sec_dbsocket,$REMOTE_ID,$APP_ID) ) {
		dbdisconnect($sec_dbsocket);
		exit;
	}
	$GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog msyslog');
        if ( ! sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) {
                dbdisconnect($sec_dbsocket);
                exit;
        }
	$dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);

	$reindex='reindex index tsyslog_pkey; reindex index host_idx; reindex index tsyslhostid_idx;reindex index tsyslogdatetime_idx; analyze tsyslog;';

	$starttime=time();
	$output=pgdatatrim(shell_exec('/usr/bin/uptime | /usr/bin/tr -s " ," "\t" | /bin/cut -f11'));
	$endtime=time();

	$SQLQuery="SELECT (relpages*8192) as size FROM pg_class where relname='tsyslog' ORDER BY relpages";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
		die(pg_errormessage()."<BR>\n");
	$size=$SQLQueryResultsObject->size;
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");

	echo "Load:  $output  Size:  $size\n";

	$starttime=time();
	if ( ( strval($output) < 3.5 ) && ( ($endtime - $starttime) < 3 ) ) { 
		if ( ( $size < 60000000 ) && ( $size > 50000000 ) ) {
			echo "Vacuum  Size:  $size  Load:  $output\n"; 
			$SQLQuery="vacuum analyze tsyslog;";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
		if ( $size <= 50000000 ) {
			echo "Vacuum Full  Size:  $size  Load:  $output\n"; 
			$SQLQuery="vacuum full analyze tsyslog; $reindex;"; 
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
		if ( $size > 20000000 ) {
			echo "Size:  $size  Load:  $output\n"; 
		}
	} else {
		echo "Size:  $size  Load:  $output\n"; 
	}
	$endtime=time();
	
	echo "Autovac operation took " . ($endtime - $starttime) . " seconds.\n";

	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
%>
