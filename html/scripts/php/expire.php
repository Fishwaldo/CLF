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
	$APP_ID=sec_appnametoid($sec_dbsocket,'SyslogOp');
	if ( ! sec_accessallowed($sec_dbsocket,$REMOTE_ID,$APP_ID) ) {
		dbdisconnect($sec_dbsocket);
		echo "Access Denined\n";
		exit;
	}
	$GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog msyslog');
        if ( ! sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) {
                dbdisconnect($sec_dbsocket);
		echo "Access Denined\n";
                exit;
        }

	$dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);

	$HeaderText="";
	$FooterText="";
	$PageTitle="";

        $SQLQuery="select * from Syslog_THost";

	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $SQLNumRows ) {
		$count=$SQLNumRows;
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {	
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$alertexpire[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_alertexpire));
			$logexpire[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_logexpire));
			$hosts[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_host));
		}
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	if ( $count ) {
		for ( $loop = 0 ; $loop != $count ; $loop++ ) {
			$dropdate=date("M-d-Y",(time() - $alertexpire[$loop]));                
			if ( $alertexpire[$loop] != 0 ) {
				$SQLQuery="begin;delete from Syslog_TAlert where TAlert_Date <= '$dropdate' and Syslog_TAlert.TSyslog_ID=Syslog_TArchive.TSyslog_ID and Syslog_TArchive.host='$hosts[$loop]';commit;";
				$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
					die(pg_errormessage()."<BR>\n");
				pg_freeresult($SQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
			}
			$dropdate=date("M-d-Y",(time() - $logexpire[$loop]));                
			if ( $logexpire[$loop] != 0 ) {
				$SQLQuery = "select * from Syslog_TArchive where date <= '$dropdate' and host='$hosts[$loop]';";
				$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
					die(pg_errormessage()."<BR>\n");
				$count2 = pg_numrows($SQLQueryResults);
				if ($count2 > 0) {
					$mydate = date("d-M-y", time());
					$handle = fopen($archivedir.'/LogArchive-'.$mydate.'.smt', "a") or 
						die("Failed To open Archive File\n");
					for ( $myloop = 0 ; $myloop != $count2 ; $myloop++) {
						$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$myloop) or
							die(pg_errormessage()."<BR>\n");
						$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tsyslog_id));
						$date=stripslashes(pgdatatrim($SQLQueryResultsObject->date));
						$time=stripslashes(pgdatatrim($SQLQueryResultsObject->time));
						$host=stripslashes(pgdatatrim($SQLQueryResultsObject->host));
						$message=stripslashes(pgdatatrim($SQLQueryResultsObject->message));
						$vseverity=verboseseverity(stripslashes(pgdatatrim($SQLQueryResultsObject->severity)));
						$vfacility=verbosefacility(stripslashes(pgdatatrim($SQLQueryResultsObject->facility)));
						fwrite($handle, "$date $time $host $vfacility $vseverity $message\n");
					}
				}
				pg_freeresult($SQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");

				$SQLQuery="begin;delete from Syslog_TArchive where date <= '$dropdate' and host='$hosts[$loop]';commit;";
				$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
					die(pg_errormessage()."<BR>\n");
				pg_freeresult($SQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
			}
		}
	}
	if ($handle) {
		fclose($handle);
		$cmd = "md5sum ".$archivedir."/LogArchive-".$mydate.".smt";
		$md5log = $archivedir."/MD5ChkSum-".$mydate.".txt";
		$handle = fopen($md5log, "a");
		@fwrite($handle, @system(escapeshellcmd($cmd))."\n");
		fclose($handle);
	}
	
	$dropdate=date("M-d-Y",(time()));
	$SQLQuery="begin;delete from Syslog_TSaveData where Syslog_TSaveData.TSave_ID=Syslog_TSave.TSave_ID and Syslog_TSave.TSave_ExpireDate <= '$dropdate';commit;"; 
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");


	$SQLQuery="begin;delete from Syslog_TSave where Syslog_TSave.TSave_ExpireDate <= '$dropdate';commit;"; 
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");

	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
%>