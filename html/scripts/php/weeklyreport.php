#!/usr/bin/php -q
<?
define("REPORTADDRESS", "cscgiss1@maybank.com.sg");
/*=============================================================================
 * $Id$
 *
 * Copyright 2004 Justin Hammond  jhammond4@csc.com
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

	$month=date("M",time());
	$day=date("d",time())+1;
	$year=date("Y",time());
	$endday = date("d", (time()-(86400*7)));

	$endtoday="$month-$day-$year";
	$startdate="$month-$endday-$year";


	$SQLQuery="select tr.reviewer, tr.date as reviewdate, tr.comments, tr.tsummary_id, ts.host, ts.date as reportdate from syslog_treview as tr, syslog_tsummary as ts where tr.tsummary_id=ts.tsummary_id and tr.date >= '$startdate' and tr.date <= '$endtoday' order by ts.host, ts.date";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage());
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $SQLNumRows ) {
		$hosttext="LogReview Report for Period of $startdate to $endtoday\n";
		$id = 0;
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			if ($id != $SQLQueryResultsObject->tsummary_id) { 
				$hosttext .=  "\n\n=============================================================\n";
				$hosttext .= "System: " .stripslashes(pgdatatrim($SQLQueryResultsObject->host)) . "\n" ;
				$hosttext .= "Report Date: ". pgdatatrim($SQLQueryResultsObject->reportdate) ."\n";
				$id = $SQLQueryResultsObject->tsummary_id;
				$hosttext .=  "=============================================================\n";
			} else {
				$hosttext .= "-------------------------------------------------------------\n";
			}
			$hosttext .= "Reviewer: ". sec_username($sec_dbsocket, $SQLQueryResultsObject->reviewer);
			$hosttext .= " Date: ". $SQLQueryResultsObject->reviewdate. "\n\n";
			$hosttext .= stripslashes(pgdatatrim($SQLQueryResultsObject->comments)) ."\n";
		}
		echo $hosttext;
		mail(REPORTADDRESS,"CLF Log Review Report",$hosttext);
	} else {
		echo "No results\n";
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage());

	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
