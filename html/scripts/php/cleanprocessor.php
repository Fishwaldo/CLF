#!/usr/bin/php -q
<?
define("REPORTADDRESS", "justin@dynam.ac");
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
		if ( ($testmailid = ismailopen($dbsocket,$REMOTE_ID) ) && ( idexist($sec_dbsocket,"Secframe_TLogin","TLogin_ID",$REMOTE_ID) ) ) {
			$begintime = time();
			$maildate=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TMail","TMail_Date","TMail_ID=$testmailid")));
			$mailtime=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TMail","TMail_Time","TMail_ID=$testmailid")));
			$SQLQuery="select distinct TProcess_ID,Syslog_TProcess.THost_ID from Syslog_TProcess,Syslog_TProcessorProfile where ( ( Syslog_TProcessorProfile.TLogin_ID=$REMOTE_ID ) and ( Syslog_TProcessorProfile.THost_ID=Syslog_TProcessorProfile.THost_ID) )";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
			echo "Got $SQLNumRows to check\n";
			$PurgeQuery="Begin ; ";
			if ( $SQLNumRows ) {
				for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
					$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
						die(pg_errormessage()."\n");
					$cleanid=stripslashes(pgdatatrim($SQLQueryResultsObject->tprocess_id));
					$cleanhost=gethost($dbsocket,stripslashes(pgdatatrim($SQLQueryResultsObject->thost_id)));
					$PurgeQuery = $PurgeQuery . "delete from Syslog_TAlert where Syslog_TAlert.TSyslog_ID=TSyslog.TSyslog_ID and TSyslog.TSyslog_ID > $cleanid and TSyslog.host='$cleanhost' ; ";
					$PurgeQuery = $PurgeQuery . "delete from Syslog_TArchive where TSyslog_ID > $cleanid and host='$cleanhost' ; ";
				}
				$PurgeQuery = $PurgeQuery . "commit ; ";
				$PurgeSQLQueryResults = pg_exec($dbsocket,$PurgeQuery) or
                                              	die(pg_errormessage()."\n");
			}
			$endtime=time();
			if ( ($endtime - $begintime) != 0 ) { 
		        	echo "Data Cleaned in " . ($endtime - $begintime) . " seconds.  " . ( $SQLNumRows / ($endtime - $begintime) ) . " rows/sec\n";
			} else {
				echo "Data loaded in 0 seconds.  Loaded $SQLNumRows.\n";
			}

			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "\n");
			cleanemail($dbsocket,$testmailid);
			clearlaunchqueue($dbsocket,$testmailid);
			closeopenmail($dbsocket,$testmailid);
			if ( $PurgeSQLQueryResults ) {
				echo "SUCCESS!!\n";
				pg_freeresult($PurgeSQLQueryResults) or
					die(pg_errormessage() . "\n");
			} else {
				echo "FAILED!!\n";
				pg_freeresult($PurgeSQLQueryResults) or
					die(pg_errormessage() . "\n");
			}
		} else {
			echo "The processor you've selected is not stale!\n";
		}

	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
