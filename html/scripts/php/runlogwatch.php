#!/usr/bin/php
<?
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
	$dodel = 0;
	if (!isset($date)) {
		$date = "yesterday";
		$dodel = 1;
	}
	if ($argc > 1) {
		$date = $argv[1];
		$dodel = 1;
	}

	$sec_dbsocket=sec_dbconnect();
	$REMOTE_ID=sec_usernametoid($sec_dbsocket,'msyslog');
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog msyslog'); 
        if ( ! sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) {
                dbdisconnect($sec_dbsocket);
                exit;
        }
        $dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);
	set_time_limit(0);

	if ($dodel = 1) {
		$SQLQuery = "delete from syslog_treview WHERE syslog_tsummary.date='$date' and syslog_treview.tsummary_id=syslog_tsummary.tsummary_id";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLQuery = "delete from syslog_tsummary where date='$date'";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
	}
	$SQLQuery="select thost_host from syslog_thost where do_logreport = 1";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
        $SQLNumRows = pg_numrows($SQLQueryResults);
        if ( $SQLNumRows ) {
        	for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
                	$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$host=pgdatatrim($SQLQueryResultsObject->thost_host);
			echo "Running Logwatch for $host\n";
			echo system("/etc/log.d/bin/parselog.sh $host $date")."\n";
                }
        }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");

	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
