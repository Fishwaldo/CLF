<?

require_once('calendar.php');
require_once('config.php');
$sec_dbsocket=sec_dbconnect();

$REMOTE_ID=sec_usernametoid($sec_dbsocket,$_SERVER['REMOTE_USER']);
$APP_ID=sec_appnametoid($sec_dbsocket,'SyslogOp');
if ( ! sec_accessallowed($sec_dbsocket,$REMOTE_ID,$APP_ID) ) {
	dbdisconnect($sec_dbsocket);
        exit;
}
        $group=0;
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Customer');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=1; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Analyst');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=2; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Administrators');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=3; }
	
	$dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);

    	$time = time();
	if (!isset($year)) {
		$year = date('Y', $time);
	}

	if (!isset($month)) {
		$month = date('n', $time);
	} else {
		if ($month == 0) {
			$month = 12;
			$year = $year -1;
		} 
		if ($month == 13) {
			$month = 1;
			$year = $year +1;
		}
	}
	do_header("Log Summary Reports", 'logwatch');

function echo_datelink($year, $month, $day) {
	return "year=$year&month=$month&day=$day";
}

function display_ticks($req, $done) {
	global $sec_dbsocket;
	if ($done < $req) {
		return "$done<img src=/images/no.gif>";
	} else {
		return "$done<img src=/images/ok.gif>";
	}
}
	if ($month < 1) {
		$year = $year -1;
		$month = 12 + $month;
	}
	if ($month > 12) {
		$year = $year+1;
		$month = $month - 12;
	}

?>
<table width=100% border=1>
<?
if (!isset($view)) {
?>
	<tr><td align=left><a href="?month=<? echo $month-6; ?>&year=<? echo $year; ?>">&lt Previous</a></td><td></td><td align=right><a href="?month=<? echo $month+6; ?>&year=<? echo $year; ?>">Next &gt</a></td></tr>
<?
}


	if (!isset($view)) {
		for ($loop1 = -5; $loop1 != 1; $loop1++) {
			if (($loop1 == -5) || ($loop1 == -2)) {
				echo "<tr>";
			}
			echo "<td>";
			$myear = $year;
			$tmp2 = $month + $loop1;
			if ($tmp2 < 1) {
				$myear = $myear -1;
				$tmp2 = 12 + $tmp2;
			}		
			if ($tmp2 > 12) {
				$myear = $myear +1;
				$tmp2 = $tmp2 - 12;
			}
			$myear2 = $myear;
			$tmp = $tmp2 + 1;
			if ($tmp > 12) {
				$tmp = $tmp - 12;
			}

			$sql = "select date_part('day', date) as day, date_part('month', date) as month, * from syslog_tsummary lw, syslog_thost h where lw.host = h.thost_host and (date >= '$myear/$tmp2/01' and date < '$myear2/$tmp/01') order by date;";
		        $SQLQueryResults = pg_exec($dbsocket,$sql) or
        		        die(pg_errormessage()."<BR>\n");
		        $SQLNumRows = pg_numrows($SQLQueryResults);
			$days = array();
	        	for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
		        	$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
                			die(pg_errormessage()."<BR>\n");
				$host = $SQLQueryResultsObject->thost_id;
				$tsid = $SQLQueryResultsObject->tsummary_id;
				$sql2 = "select * from syslog_treview where tsummary_id = $tsid";
				$SQLQueryResults2 = pg_exec($dbsocket, $sql2) or 
					die(pg_errormessage()."<BR>");
				if ( ( $group >= 2 ) || ( (logincanseehost($dbsocket,$REMOTE_ID,$host)) && $group == 1 ) ) {
					$myday = $SQLQueryResultsObject->day;
					$today = date('d', $time);
					$mnt2 = date('m', time());
					if (($tmp2 < $mnt2) || ($today - $myday > 2)) {
 						if (@pg_numrows($SQLQueryResults2) < $SQLQueryResultsObject->log_reviewers) {
							$var = array("?".echo_datelink($year, $tmp2, $myday), 'highlight-day');
						} else {
							$var = array("?".echo_datelink($year, $tmp2, $myday), 'light-day');
						}
					} else {
						$var = array("?".echo_datelink($year, $tmp2, $myday), 'linked-day');
					}
					$days[$myday] = $var;
				}
	        	}
		    	echo generate_calendar($myear, $tmp2, $days, 3);
		    	echo "</td>";
			if (($loop1 == -3) || ($loop1 == 0)) {
				echo "</tr>";
			}
	    	}
	    if (isset($day)) {
		$tmp2 = $month + 1;
		$sql = "select date_part('day', date) as day, date_part('month', date) as month, * from syslog_tsummary lw, syslog_thost h where lw.host = h.thost_host and (date >= '$year/$month/01' and date < '$year/$tmp2/01') order by date;";
	        $SQLQueryResults = pg_exec($dbsocket,$sql) or
       		        die(pg_errormessage()."<BR>\n");
	        $SQLNumRows = pg_numrows($SQLQueryResults);
		echo "<h2>Available Logwatch Reports on $day/$month/$year for ".sec_username($sec_dbsocket, $REMOTE_ID)." </h2>";
		echo "</td></tr><tr><td>Host</td><td>Reviews Required</td><td>Reviews Performed</td></tr>";
	        for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
		        $SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
                		die(pg_errormessage()."<BR>\n");
			$host = $SQLQueryResultsObject->thost_id;
			$hostname = $SQLQueryResultsObject->thost_host;
			$reportid = $SQLQueryResultsObject->tsummary_id;
			$revreq = $SQLQueryResultsObject->log_reviewers;
			$sql2 = "select * from syslog_treview where tsummary_id = $reportid";
			$SQLQueryResults2 = pg_exec($dbsocket, $sql2) or
				die(pg_errormessage()."<BR>");
			$cnt = @pg_numrows($SQLQueryResults2);
			if ($SQLQueryResultsObject->day == $day) {
	 			if ( ( $group >= 2 ) || ( (logincanseehost($dbsocket,$REMOTE_ID,$host)) && $group == 1 ) ) {
					echo "<tr><td><a href=?view=$reportid&".echo_datelink($year, $month, $day).">$hostname</a></td><td>".$revreq."</td><td>".display_ticks($revreq, $cnt)."</td></tr>";
				}
			}
		}
	    }
    }
    if (isset($view)) {
	if (isset($action)) {
		if ($action == 'Complete Review') {
			if ($donerev == 0) {
				$sql = "insert into syslog_treview (reviewer, date, tsummary_id, comments) values ($REMOTE_ID, 'NOW()', $view, '$comment')";
				echo "<tr><td colspan=3 align=center><h2>Review Completed</h2></td></tr>";
			} else {
				$sql = "update syslog_treview set comments='$comment' where id=$donerev";
				echo "<tr><td colspan=3 align=center><h2>Review Updated</h2></td></tr>";
			}

			pg_exec($dbsocket, $sql) or 
				die(pg_errormessage()."<BR>");
		} else {
			echo "<tr><td colspan=3 align=center><h2>Review Aborted</h2></td></tr>";
		}
	}
	$sql = "select * from syslog_tsummary ts, syslog_thost h where ts.tsummary_id = $view and ts.host=h.thost_host order by ts.date";
        $SQLQueryResults = pg_exec($dbsocket,$sql) or
                die(pg_errormessage()."<BR>\n");
        $SQLNumRows = pg_numrows($SQLQueryResults);
	if ($SQLNumRows > 0) {
	        $SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
                	die(pg_errormessage()."<BR>\n");
		$hostname = $SQLQueryResultsObject->thost_host;
		$report = stripslashes(nl2br($SQLQueryResultsObject->data));
		$date = $SQLQueryResultsObject->date;
		$sql = "select * from syslog_treview where tsummary_id=$SQLQueryResultsObject->tsummary_id order by date";
		$SQLQueryResults = pg_exec($dbsocket, $sql) or 
			die(pg_errormessage()."<BR>");
		$numrows = pg_numrows($SQLQueryResults);
		$mycomment = "";
		$donerev = 0;
		if ($numrows > 0 ) {
			echo "<tr><th>Reviewer</th><th>Comments</th><th>Date</th></tr>";
		}
		for ($loop = 0; $loop != $numrows; $loop++) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults, $loop) or 
				die(pg_errormessage()."<BR>");
			if ($SQLQueryResultsObject->reviewer == $REMOTE_ID) {
				$mycomment = stripslashes($SQLQueryResultsObject->comments);
				$donerev = $SQLQueryResultsObject->id;
			}
			$reviewer = sec_username($sec_dbsocket, $SQLQueryResultsObject->reviewer);
			$comments = stripslashes(nl2br($SQLQueryResultsObject->comments));
			$date = $SQLQueryResultsObject->date;
			echo "<tr><td align=center>$reviewer</td><td align=left>$comments</td><td align=center>$date</td></tr>";
		}
		echo "<tr><td colspan=3><hr></td></tr><tr><td colspan=3><h3>Logwatch report for $hostname on $date</h3></td></tr>";
		echo "<tr><td colspan=3 bgcolor=gray>$report</td></tr>";
	}
	
   	/* now the feedback form only to update or insert one comment per reviewer*/
	echo "<tr><td colspan=2>";
	openform("logwatch.php", "post", 0, 0, 0);
	formfield("donerev", "hidden", 3, 1, 0, 200, 200, $donerev);
	formfield("view", "hidden", 3, 1, 0, 200, 200, $view);
	if ($donerev > 0) {
		echo "Update ";
	}
	echo "Reviewer Comments:<br><textarea rows=10 cols=70 name=comment>$mycomment</textarea></td><td>";
	formsubmit("Complete Review");
	echo "<br>";
	formsubmit("Abort Review");
	closeform();
	echo "</td></tr>";
	




   }


?>
</td></tr></table>
<?php
do_footer();
?>