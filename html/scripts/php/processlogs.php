#!/usr/bin/php
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

	echo "Authenticated\n";

	if ( idexist($dbsocket,"Syslog_TSuspend","TLogin_ID",$REMOTE_ID) ) {
		echo "Processor Suspended!  Quitting....\n";
		dbdisconnect($dbsocket);
		dbdisconnect($sec_dbsocket);
		exit;
	}
	$myflock = fopen($lockfile, "w+");
	if (!flock($myflock, LOCK_EX|LOCK_NB)) {
		echo "Locked Processor.\n";
		if ((time() - filemtime($lockfile)) > ($locktime * 60 * 60)) {
			mail(WARNINGADDRESS,"SMT WARNING:  Locked Processor","SMT Processor:  $REMOTE_ID\nThe SMT system processor has been locked for longer than $locktime hours.\nThis could be caused by one of three things:\n1.  Regularlary scheduled maintenance is keeping the database busy afterwhich you should not longer see this warning.\n2.  The log processor crashed and will require manual fixing. (check if processlogs.php is running, if not delete /tmp/processor.lock\n3.  The overall load of the box is too great and may need to be resized.\n\nPlease see the appropriate support documentation to help determine which of these three it is.\n\nSincerely, SMT-Auto Message");
		}
		dbdisconnect($dbsocket);
		dbdisconnect($sec_dbsocket);
		exit;
	}
	if ( ($testmailid = ismailopen($dbsocket,$REMOTE_ID)) ) {
		echo "Found what appears to be a stale connection.\n";
		if (0) {
			cleanemail($dbsocket,$testmailid);
			clearlaunchqueue($dbsocket,$testmailid);
			closeopenmail($dbsocket,$testmailid);
			exit;
		}
		$begintime = time();
		$maildate=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TMail","TMail_Date","TMail_ID=$testmailid")));
		$mailtime=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TMail","TMail_Time","TMail_ID=$testmailid")));
		$SQLQuery="select distinct TProcess_ID,Syslog_TProcess.THost_ID from Syslog_TProcess,Syslog_TProcessorProfile where ( ( Syslog_TProcessorProfile.TLogin_ID=$REMOTE_ID ) and ( Syslog_TProcessorProfile.THost_ID=Syslog_TProcessorProfile.THost_ID) )";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		echo "Got $SQLNumRows to check\n";
		$PurgeQuery="Begin ; ";
		$mcount = 0;
		if ( $SQLNumRows ) {
			for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."\n");
				$cleanid=stripslashes(pgdatatrim($SQLQueryResultsObject->tprocess_id));
				$cleanhost=gethost($dbsocket,stripslashes(pgdatatrim($SQLQueryResultsObject->thost_id)));
				$PurgeQuery="Begin ; ";
				$PurgeQuery = $PurgeQuery . "delete from Syslog_TAlert where Syslog_TAlert.TSyslog_ID=TSyslog.TSyslog_ID and TSyslog.TSyslog_ID > $cleanid and TSyslog.host='$cleanhost' ; ";
				$PurgeQuery = $PurgeQuery . "delete from Syslog_TArchive where TSyslog_ID > $cleanid and host='$cleanhost' ; ";
				$PurgeQuery = $PurgeQuery . "commit ; ";
				$PurgeSQLQueryResults = pg_exec($dbsocket,$PurgeQuery) or
                                      	die(pg_errormessage()."\n");
				$count = pg_affected_rows($PurgeSQLQueryResults);
				$mcount = $mcount + $count;
				echo "Cleaned $cleanhost of $count records\n";
			}
		}
		$endtime=time();
		if ( ($endtime - $begintime) != 0 ) { 
	        	echo "Data Cleaned in " . ($endtime - $begintime) . " seconds.  " . ( $mcount / ($endtime - $begintime) ) . " rows/sec\n";
		} else {
			echo "Data loaded in 0 seconds.  Cleaned $mcount.\n";
		}

		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "\n");
		cleanemail($dbsocket,$testmailid);
		clearlaunchqueue($dbsocket,$testmailid);
		closeopenmail($dbsocket,$testmailid);
		if ( $PurgeSQLQueryResults ) {
			echo "SUCCESS!!\n";
			$ok = 1;
			pg_freeresult($PurgeSQLQueryResults) or
				die(pg_errormessage() . "\n");
		} else {
			echo "FAILED!!\n";
			$ok = 2;
			pg_freeresult($PurgeSQLQueryResults) or
				die(pg_errormessage() . "\n");
		}

		$maildate=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TMail","TMail_Date","TMail_ID=$testmailid")));
		$mailtime=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TMail","TMail_Time","TMail_ID=$testmailid")));
		$testhour=substr($mailtime,0,2);
		$testminute=substr($mailtime,3,2);
		$testsecond=substr($mailtime,6,2);
		$testmonth=substr($maildate,5,2);
		$testday=substr($maildate,8,2);
		$testyear=substr($maildate,0,4);
		$mailunixtime=mktime($testhour,$testminute,$testsecond,$testmonth,$testday,$testyear);
		$currentunixtime=time();
		if ( ( $currentunixtime - $mailunixtime ) > 3600 ) {
			if ($ok = 1) {
				mail(WARNINGADDRESS,"SMT WARNING:  Stale or Overrun Processor cleaned","SMT Processor:  $REMOTE_ID\nThe SMT system ran autorecovery.\nThis could be caused by one of three things:\n1.  Regularlary scheduled maintenance is keeping the database busy afterwhich you should not longer see this warning.\n2.  The log processor crashed and will require manual fixing.\n3.  The overall load of the box is too great and may need to be resized.\n\nPlease see the appropriate support documentation to help determine which of these three it is.\n\nSincerely, SMT-Auto Message");
			} else {
				mail(WARNINGADDRESS,"SMT ERROR:  Stale or Overrun Processor","SMT Processor:  $REMOTE_ID\nThe SMT system cannot process logs at the moment.\nThis could be caused by one of three things:\n1.  Regularlary scheduled maintenance is keeping the database busy afterwhich you should not longer see this warning.\n2.  The log processor crashed and will require manual fixing.\n3.  The overall load of the box is too great and may need to be resized.\n\nPlease see the appropriate support documentation to help determine which of these three it is.\n\nSincerely, SMT-Auto Message");
			}
		}
		dbdisconnect($dbsocket);
		dbdisconnect($sec_dbsocket);
		flock($myflock, LOCK_UN);
		fclose($myflock);
		unlink("/tmp/processor.lock");
		exit;
	} else {
		echo "No stale data, proceeding.\n";
		$maildate=date("M-d-Y",time());
		$mailtime=date("G:i:s",time());
		$mailid=openmail($dbsocket,$maildate,$mailtime,$REMOTE_ID);
	}

	$SQLQuery="select Syslog_THost.THost_ID,Syslog_THost.THost_Rate,Syslog_THost.THost_Host from Syslog_THost,syslog_tprocessorprofile where ( Syslog_TProcessorProfile.TLogin_ID=$REMOTE_ID ) and ( Syslog_TProcessorProfile.THost_ID=Syslog_THost.THost_ID ) and ( Syslog_TProcessorProfile.TLogin_ID=$REMOTE_ID )";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."\n");

	$SQLNumRows = pg_numrows($SQLQueryResults);
	$numhosts=0;
	if ( $SQLNumRows > 0 ) {
		$numhosts = $SQLNumRows;
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
                        $SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."\n");
			$hostname[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_host)); 
			$hostnameids[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_id));
			$hostrate[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_rate));
			if ( $hostrate[$loop] < 100 ) { $hostrate[$loop] = 100; }
		}
	}

	echo "Building host rule cache\n";
	$SQLQuery="select TRule_ID,TRule_LogAlert,TRule_Email,TRule_Expression,TRule_Desc,TRule_RuleOrLevel,TRule_StartFacility," .
		"TRule_StopFacility,TRule_StartSeverity,TRule_StopSeverity,Syslog_THost.THost_Host,Syslog_THost.THost_ID,Syslog_TRule.TLaunch_ID,TRule_Threshold,TRule_ThresholdType,TRule_StartTime,TRule_EndTime," .
		"TRule_TimerType,TRule_DaysofWeek from Syslog_TRule,Syslog_TProcessorProfile,Syslog_THost where ( Syslog_TProcessorProfile.TLogin_ID=$REMOTE_ID ) and " .
		"( Syslog_TProcessorProfile.THost_ID=Syslog_TRule.THost_ID ) and ( Syslog_TRule.THost_ID=Syslog_THost.THost_ID) order by THost_Host,TRule_ID"; 

	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);

	echo "Found $SQLNumRows rules\n";
	$NumRules=$SQLNumRows;
	$ruleemailcount="";
	if ( $SQLNumRows > 0 ) {
		$workhost="";
		$numrules=$SQLNumRows;
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."\n");
			$temphost=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_host));
			$temphostids=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_id));
			if ( $workhost != $temphost ) {
				$workhost = $temphost;
				echo "$numhosts Host:  $temphost\n";

	                        for ( $hostloop = 0 ; $hostloop != count($hostname) ; $hostloop++ ) {
        	                        if ( $hostname[$hostloop] == $workhost ) { $workhostid=$hostloop; }
                	        }
				$toprule[$workhostid]=$loop;
				$bottomrule[$workhostid]=$loop;
				$hostprocid[$workhostid]=0;
				$hosttotalproc[$workhostid]=0;
			} else { $bottomrule[$workhostid]=$loop; }
			$ruleid[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_id));
			$rulelogalert[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_logalert));
			$ruleemail[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_email));
			$ruleemailcount1 = array ( $ruleemail[$loop] => 0 ); 
			$ruleemailcount=array_merge($ruleemailcount,$ruleemailcount1);	
			$ruleexpression[$loop]=pgdatatrim($SQLQueryResultsObject->trule_expression);
			$ruledesc[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_desc));
			$ruleruleorlevel[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_ruleorlevel));
			$rulestartfacility[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_startfacility));
			$rulestopfacility[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_stopfacility));
			$rulestartseverity[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_startseverity));
			$rulestopseverity[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_stopseverity));
			$rulehost[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->host));
			$rulelaunchid[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tlaunch_id));
			$rulethreshold[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_threshold));
			$rulethresholdtype[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_thresholdtype));
			$rulethresholdcount[$loop]=0;
			$rulestarttime[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_starttime));
			$ruleendtime[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_endtime));
			$ruletimertype[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_timertype));
			$ruledaysofweek[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_daysofweek));
			$ruledenytop[$loop]=="";
		}
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "\n");

	echo "Loading denial rules\n";

	$SQLQuery="select syslog_truledeny.truledeny_expression,syslog_truledeny.truledeny_startfacility," . 
		"syslog_truledeny.truledeny_stopfacility,syslog_truledeny.truledeny_startseverity," . 
		"syslog_truledeny.truledeny_stopseverity,syslog_truledeny.trule_id from Syslog_TRule," . 
		"Syslog_TProcessorProfile,Syslog_TRuleDeny where " . 
		"( Syslog_TProcessorProfile.THost_ID=Syslog_TRule.THost_ID ) and " . 
		"( Syslog_TProcessorProfile.TLogin_ID=$REMOTE_ID ) and " . 
		"( Syslog_TRule.TRule_ID=Syslog_TRuleDeny.TRule_ID ) order by syslog_truledeny.trule_id";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo "Found $SQLNumRows deny rules\n";

	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."\n");
			$newid=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_id));
			if ( $ruledenytop[$newid] == "" ) { 
				echo "Rule ID:  $newid  start deny ID:  " . $loop+1 . "\n";
				$ruledenytop[$newid]=$loop+1; 
			}
			$ruledenybottom[$newid]=$loop+1;
			$ruledenyexp[$loop+1]=pgdatatrim($SQLQueryResultsObject->truledeny_expression);
			echo $loop+1 . " Deny Rule Expression:  " . $ruledenyexp[$loop+1] . "\n";
			$ruledenystartfacility[$loop+1]=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_startfacility));
			$ruledenystopfacility[$loop+1]=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_stopfacility));
			$ruledenystartseverity[$loop+1]=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_startseverity));
			$ruledenystopseverity[$loop+1]=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_stopseverity));
		}
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "\n");

	$SQLQuery="select distinct on ( host, TSyslog_ID ) TSyslog.TSyslog_ID, TSyslog.host, TSyslog.date, TSyslog.time, TSyslog.message" .
		", TSyslog.severity, TSyslog.facility from TSyslog,syslog_thost,Syslog_TProcess,Syslog_TProcessorProfile where ( " . 
		"( TSyslog_ID > Syslog_TProcess.TProcess_ID ) and ( Syslog_TProcess.THost_ID = Syslog_THost.THost_ID ) and " . 
		"( Syslog_THost.THost_Host = TSyslog.host ) and ( Syslog_TProcessorProfile.TLogin_ID=$REMOTE_ID ) and " . 
		" ( TSyslog.host = Syslog_THost.THost_Host ) and ( Syslog_TProcessorProfile.THost_ID = Syslog_THost.THost_ID ) ) order by host, TSyslog_ID limit $SQLLIMIT"; 
	echo "SQL Query:  $SQLQuery<BR>\n";
	echo "Grabbing Syslog data...";

	$begintime=time();

	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	$SyslogRows = $SQLNumRows;
	if ( $SQLNumRows == 0 ) {
		echo "Done.\n  Found $SQLNumRows rows.\n";
		closeopenmail($dbsocket,$mailid);
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "\n");
		dbdisconnect($dbsocket);
		dbdisconnect($sec_dbsocket);
		flock($myflock, LOCK_UN);
		fclose($myflock);
		unlink("/tmp/processor.lock");
		exit;

	}
	echo "Done.\n  Found $SQLNumRows rows.\n";
       
        $endtime=time();
	if ( ($endtime - $begintime) != 0 ) { 
        	echo "Data loaded in " . ($endtime - $begintime) . " seconds.  " . ( $SQLNumRows / ($endtime - $begintime) ) . " rows/sec\n";
	} else {
		echo "Data loaded in 0 seconds.  Loaded $SQLNumRows.\n";
	}
	$begintime=time();

	$email=0;
	$alert=0;
	$workhost="";
	$rulehostid="";

	$archivecommit="begin; ";
	for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
		$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
			die(pg_errormessage()."\n");
		$globalalert=0;
		$globalmatchedexpression="";
		$globalid=0;
		$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tsyslog_id));
		$date=stripslashes(pgdatatrim($SQLQueryResultsObject->date));
		$time=stripslashes(pgdatatrim($SQLQueryResultsObject->time));
		$host=stripslashes(pgdatatrim($SQLQueryResultsObject->host));
		$message=pgdatatrim($SQLQueryResultsObject->message);
		$severity=pgdatatrim($SQLQueryResultsObject->severity);
		$facility=pgdatatrim($SQLQueryResultsObject->facility);

		if ( strlen($archivecommit) < 64000 ) {
			$tempmessage=str_replace("\\", "\\\\", $message);
			$tempmessage=str_replace("'", "''", $tempmessage);
			$archivecommit = $archivecommit . " insert into Syslog_TArchive values ($id,$facility,$severity,'$date','$time','$host','$tempmessage'); "; 
		} else {
			$archivecommit = $archivecommit . " commit; ";
			echo "Committing data block:  " . strlen($archivecommit) . " bytes.  Row $loop of $SQLNumRows.\n";
			$TempSQLQueryResults = pg_exec($dbsocket,$archivecommit) or
				die(pg_errormessage()."\n");
			pg_freeresult($TempSQLQueryResults) or
				die(pg_errormessage() . "\n");
			$archivecommit = "begin;";
		}
		if ( $workhost != $host ) {
			echo "New Host:  $host\n";
			$workhost=$host;
			$rulehostid="";
			for ( $hostloop = 0 ; $hostloop != (count($hostname)) ; $hostloop++ ) {
				if ( $hostname[$hostloop] == $host ) { $rulehostid=$hostloop; } 
			}
		}
		$email=0;
		$alert=0;
		$launch=0;

		if ( strlen($toprule[$rulehostid]) > 0 ) {
			$loop1=$toprule[$rulehostid];
			while ( $loop1 <= $bottomrule[$rulehostid] ) {
				$matchedrule=$ruleexpression[$loop1];
				$ruleorlevel=$ruleruleorlevel[$loop1];
				$startfacility=$rulestartfacility[$loop1];
				$stopfacility=$rulestopfacility[$loop1];
				$startseverity=$rulestartseverity[$loop1];
				$stopseverity=$rulestopseverity[$loop1];
				$logalerts=$rulelogalert[$loop1];
				$emails=$ruleemail[$loop1];
				$descs=$ruledesc[$loop1];
				$launchid=$rulelaunchid[$loop1];
				$timertype=$ruletimertype[$loop1];
				$starttime=$rulestarttime[$loop1];
				$endtime=$ruleendtime[$loop1];
				$daysofweek=$ruledaysofweek[$loop1];
                                if ( $matchedrule != "" ) {
                                        $regresults=ereg($matchedrule,$message);
                                } else {
                                        $regresults=0;
                                }

				/* $regresults=ereg($matchedrule,$message); */
				$bounds=withinbounds($facility,$severity,$startfacility,$stopfacility,$startseverity,$stopseverity);
				if ( ( ( $ruleorlevel == 1 ) && ( $regresults ) ) || 
					( ( $ruleorlevel == 2 ) && ( $regresults ) && ( $bounds ) ) ||
					( ( $ruleorlevel == 3 ) && ( $bounds ) ) ) { 
					
					$matchedexpression=$matchedrule;
					if ( $logalerts ) { $alert= 1; }
					if ( $launchid ) { $launch= 1; }
					if ( $emails != "" ) { 
						$email=1; 
						$emailaddress=$emails;
						$desc=$descs;
					}
	                		$postdate=date("M-d-Y",time());
					$posttime=date("G:i:s",time());
				}

				/* convert date & time to obtain seconds since 1970 so that we may pass that to suppressruleresults */
				$dateyear=substr($date,0,4);
				$datemonth=substr($date,5,2);
				$dateday=substr($date,8,2);
				$timehour=substr($time,0,2);
				$timeminute=substr($time,3,2);
				$timesec=substr($time,6,2);

				$timestamp=mktime($timehour,$timeminute,$timesec,$datemonth,$dateday,$dateyear);	

				if ( ( $alert ) || ( $email ) || ( $launch ) ) {
					if ( supressruleresults($starttime,$endtime,$daysofweek,$timertype,$timestamp) ) {
						$alert=0;
						$email=0;
						$launch=0;
					}
				}
				if ( ( ( $alert ) || ( $email ) || ( $launch ) ) && ( ! supressruleresults($starttime,$endtime,$daysofweek,$timertype,$timestamp) ) ) {
					$rid=$ruleid[$loop1];

					if ( $rulethresholdtype[$loop1] ) {
						$rulethresholdcount[$loop1]++;
					}

					if ( $ruledenytop[$rid] != "" ) { 
						$loop2=$ruledenytop[$rid];
						while ( $loop2 <= $ruledenybottom[$rid] ) {
							$bounds=withinbounds($facility,$severity,
								$ruledenystartfacility[$loop2],
								$ruledenystopfacility[$loop2],
								$ruledenystartseverity[$loop2],
								$ruledenystopseverity[$loop2]);
							if ( $ruledenyexp[$loop2] != "" ) {
								$denyresults=ereg($ruledenyexp[$loop2],$message);
							} else {
								$denyresults="";
							}
							if ( ( $bounds ) && ( $denyresults ) ) {
								/* echo "Supressing $message matched by '$matchedrule' with  Deny ID:  $loop2\n"; */
								$alert=0;
								$email=0;
								$launch=0;
								$loop2=$ruledenybottom[$rid];
							}
							$loop2++;
						} 
					}
					echo "Type:  $rulethresholdtype[$loop1]  Count:  $rulethresholdtype[$loop1]\n";
					if ( ( ! $alert ) && ( ! $email ) && ( ! $launch ) && ( $rulethresholdtype[$loop1] ) ) {
						echo "No alerts, no emails, no launch... decrementing\n";
						$rulethresholdcount[$loop1]--;
					}
					if ( ( $rulethresholdcount[$loop1] != $rulethreshold[$loop1] ) && ( $rulethresholdtype[$loop1] == 2 ) ) {
						$email=0;
						$launch=0;
					}
					if ( ( $rulethresholdcount[$loop1] == $rulethreshold[$loop1] ) && ( $rulethresholdtype[$loop1] == 2 ) ) {
						$desc=$desc . "\nThe rule matched $rulethreshold[$loop1] message(s).\n";
						$rulethresholdcount[$loop1]=0;
					}
					if ( ( $rulethresholdcount[$loop1] == $rulethreshold[$loop1] ) && ( $rulethresholdtype[$loop1] == 1 ) ) {
						$desc=$desc . "\nFurther rule hits will be supressed after this log entry.  Supress after $rulethreshold[$loop1] match(es).\n";
					}
					if ( ( $rulethresholdcount[$loop1] > $rulethreshold[$loop1] ) && ( $rulethresholdtype[$loop1] == 1 ) && ( $rulethreshold[$loop1] > 0 ) ) {
						$email=0;
						$launch=0;
					}
				}
				if ( $launch ) {
					if ( ! launchassociated($dbsocket,$launchid,$id,$mailid) ) {
						addlaunchdataentry($dbsocket,$launchid,$id,$mailid,$desc);
					}
				}
				if ( $alert ) { 
					$globalalert=1;
					$globalmatchedexpression=$matchedexpression;
					$globalid=$id;
				}
				if ( $email ) { 
					if ( $ruleemailcount[$emailaddress] != $id ) {
						echo "Last ID $emailaddress was emailed was $ruleemailcount[$emailaddress]\n";
						addmail($dbsocket,$emailaddress,$mailid,$id,$desc); 
						$ruleemailcount[$emailaddress] = $id;
						echo "$emailaddress processed $ruleemailcount[$emailaddress]\n"; 
					}
				}
				$loop1++;
			}
		}
		$hostprocid[$rulehostid]=$id; 
		$hosttotalproc[$rulehostid]=$hosttotalproc[$rulehostid] + 1; 
		if ( $globalalert ) { 
			echo "Adding Alert  $globalid  $loop\n";
			addalert($dbsocket,$postdate,$posttime,$globalmatchedexpression,$globalid); 
		}
	}
	/* Commit the last set of logs over to the table */
	echo "Committing data block:  " . strlen($archivecommit) . " bytes\n";
	$archivecommit = $archivecommit . " commit; ";
	$TempSQLQueryResults = pg_exec($dbsocket,$archivecommit) or
		die(pg_errormessage()."\n");
	pg_freeresult($TempSQLQueryResults) or
		die(pg_errormessage() . "\n");

	$purgesyslogtable="begin; ";

	echo "Host Count:  " . count($hostname) . "\n";
	for ( $hostloop = 0 ; $hostloop != (count($hostname)) ; $hostloop++ ) {
		echo "$hostname[$hostloop] Total Lines Processed:  $hosttotalproc[$hostloop]  Last Entry:  $hostprocid[$hostloop]\n";
		if ( $hostprocid[$hostloop] != 0 ) {
			echo "Updating $hostname[$hostloop]:  $hostnameids[$hostloop]\n";
			/* updateprocessid($dbsocket,$hostprocid[$hostloop],$hostnameids[$hostloop]); */
			$purgesyslogtable = $purgesyslogtable . "update Syslog_TProcess set TProcess_ID=$hostprocid[$hostloop] where THost_ID='$hostnameids[$hostloop]'; ";

			$purgesyslogtable = $purgesyslogtable . "delete from TSyslog where TSyslog_ID <= $hostprocid[$hostloop] and host='$hostname[$hostloop]'; ";	
		}

		if ( $hosttotalproc[$hostloop] >= $hostrate[$hostloop] ) {
			echo "Sending warning that $hostname[$hostloop] has sent $hosttotalproc[$hostloop] since last check\n";
			mail(WARNINGADDRESS,"SMT WARNING:  Log Rate Warning:  $hostname[$hostloop]","$hostname[$hostloop] produced $hosttotalproc[$hostloop] log entries since last sample.  Threshold set to $hostrate[$hostloop].\nPlease check host as this could be a sign of a serious problem.\n\nSincerely, SMT-Auto Message");
		}
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "\n");

	echo "Finished processing syslogs, switching to emails\n";
	if ( numemailrecords($dbsocket,$mailid) ) {
		$SQLQuery = "select distinct TEmail_Email from Syslog_TEmail where TMail_ID=$mailid";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."\n");
			$clientemail=stripslashes(pgdatatrim($SQLQueryResultsObject->temail_email));
			echo "Sending email to $clientemail\n";

			$SQLQuery = "select TSyslog.TSyslog_ID,TSyslog.date,TSyslog.time,TSyslog.host,message,temail_desc from TSyslog,Syslog_TEmail where Syslog_TEmail.TEmail_Email='$clientemail' and TSyslog.TSyslog_ID=Syslog_TEmail.TSyslog_ID order by TSyslog.host,Syslog_TEmail.TSyslog_ID";
			$EmailSQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."\n");
			$EmailSQLNumRows = pg_numrows($EmailSQLQueryResults);
			$loghost="";
			for ( $loop1 = 0 ; $loop1 != $EmailSQLNumRows ; $loop1++ ) {
				$EmailSQLQueryResultsObject = pg_fetch_object($EmailSQLQueryResults,$loop1) or
					die(pg_errormessage()."\n");
				$logid=stripslashes(pgdatatrim($EmailSQLQueryResultsObject->tsyslog_id));
				$host=stripslashes(pgdatatrim($EmailSQLQueryResultsObject->host));
				$date=stripslashes(pgdatatrim($EmailSQLQueryResultsObject->date));
				$time=stripslashes(pgdatatrim($EmailSQLQueryResultsObject->time));
				$message=stripslashes(pgdatatrim($EmailSQLQueryResultsObject->message));
				$desc=stripslashes(pgdatatrim($EmailSQLQueryResultsObject->temail_desc));
				if ( $loghost == "" ) {
					$loghost=$host;
					$deliverymessage="";
				};
				if ( $loghost != $host ) {
					$results=mail($clientemail,"SMT Report:  $loghost",$deliverymessage);
					$deliverymessage="";
					$loghost=$host;
				}
				$deliverymessage=$deliverymessage . "$date $time $host $logid $message\nProblem Description/Resolution:  $desc\n";
			}
			pg_freeresult($EmailSQLQueryResults) or
				die(pg_errormessage() . "\n");
			if ( $EmailSQLNumRows > 0 ) {
				$results=mail($clientemail,"SMT Report:  $host",$deliverymessage);
			}
		}
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "\n");
	}
	echo "Cleaning up email\n";
	cleanemail($dbsocket,$mailid);
	/* Delete mail that would have been sent, equivalent to a mail queue */

	echo "Finished emails, switching to launch section\n";
	if ( numlaunchrecords($dbsocket,$mailid) ) {
		$SQLQuery = "select distinct TLaunch_ID from Syslog_TLaunchQueue where TMail_ID=$mailid";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."\n");
			$launchid=stripslashes(pgdatatrim($SQLQueryResultsObject->tlaunch_id));
			$execprogram=relatedata($dbsocket,"Syslog_TLaunch","TLaunch_Program","TLaunch_ID=$launchid");
			echo "Going to launch '$execprogram'.";

			$SQLQuery = "select TSyslog.TSyslog_ID,TSyslog.date,TSyslog.time,TSyslog.host,message,TLaunchQueue_Desc from TSyslog,Syslog_TLaunchQueue where Syslog_TLaunchQueue.TLaunch_ID='$launchid' and TSyslog.TSyslog_ID=Syslog_TLaunchQueue.TSyslog_ID order by TSyslog.host,Syslog_TLaunchQueue.TSyslog_ID";
			$LaunchSQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."\n");
			$LaunchSQLNumRows = pg_numrows($LaunchSQLQueryResults);
			$loghost="";
			for ( $loop1 = 0 ; $loop1 != $LaunchSQLNumRows ; $loop1++ ) {
				$LaunchSQLQueryResultsObject = pg_fetch_object($LaunchSQLQueryResults,$loop1) or
					die(pg_errormessage()."\n");
				$logid=stripslashes(pgdatatrim($LaunchSQLQueryResultsObject->tsyslog_id));
				$host=stripslashes(pgdatatrim($LaunchSQLQueryResultsObject->host));
				$date=stripslashes(pgdatatrim($LaunchSQLQueryResultsObject->date));
				$time=stripslashes(pgdatatrim($LaunchSQLQueryResultsObject->time));
				$message=stripslashes(pgdatatrim($LaunchSQLQueryResultsObject->message));
				$desc=stripslashes(pgdatatrim($LaunchSQLQueryResultsObject->tlaunchqueue_desc));
				if ( $loghost == "" ) {
					$loghost=$host;
					$deliverymessage="Target Host:  $host\n";
					$file="/tmp/launchprogram." . rand(0,262144) . "." . rand(0,262144);
					$fd = fopen ("$file", "w+");
				};
				if ( $loghost != $host ) {
					fwrite ( $fd, $deliverymessage , strlen($deliverymessage));
					fclose($fd);
					exec("$execprogram $file");

					$deliverymessage="Target Host:  $host\n";
					$loghost=$host;
					$file="/tmp/launchprogram." . rand(0,262144) . "." . rand(0,262144);
					$fd = fopen ("$file", "w+");
				}
				$deliverymessage=$deliverymessage . "$date $time $host $logid $message\nProblem Description/Resolution:  $desc\n";
			}
			pg_freeresult($LaunchSQLQueryResults) or
				die(pg_errormessage() . "\n");
			fwrite ( $fd, $deliverymessage , strlen($deliverymessage));
			fclose($fd);
			exec("$execprogram $file");
		}
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "\n");
	}
	echo "Cleaning up launched programs\n";
	clearlaunchqueue($dbsocket,$mailid);
	/*  Time to finally delete the log messages in the TSyslog table that we are done with.  */
	/*  Note that the system tries to process this as a whole 'delete' transaction.  If it fails, */
	/*  the logs will be kept in even though the system is finished.  This will cause problems if the */
	/*  system attempts to rerun */

        $endtime=time();
	if ( ($endtime - $begintime) != 0 ) {
        	echo "Page loaded in " . ($endtime - $begintime) . " seconds.  " . ($SyslogRows / ($endtime - $begintime) ) . " rows/sec\n";
	} else {
        	echo "Page loaded in " . ($endtime - $begintime) . " seconds.  $SyslogRows  rows/sec\n";
	}		

	echo "Purging TSyslog table\n";
	$purgebegintime=time();
	$purgesyslogtable = $purgesyslogtable . "commit;";
	echo "SQL Query:  $purgesyslogtable<BR>\n";
	$SQLQueryResults = pg_exec($dbsocket,$purgesyslogtable) or
		die(pg_errormessage()."\n");
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "\n");
        $purgeendtime=time();
	if ( ($purgeendtime - $purgebegintime) != 0 ) {
        	echo "Data purged @ " . ($purgeendtime - $purgebegintime) . " seconds.  " . ($SyslogRows / ($purgeendtime - $purgebegintime) ) . " rows/sec\n";
	} else {
        	echo "Data purged @ " . ($purgeendtime - $purgebegintime) . " seconds.  $SyslogRows  rows/sec\n";
	}		

	clearlaunchqueue($dbsocket,$testmailid);
	closeopenmail($dbsocket,$mailid);
	echo "Finished cleaning up email\n";

	dbdisconnect($dbsocket);
	dbdisconnect($sec_dbsocket);
	flock($myflock, LOCK_UN);
	fclose($myflock);
	unlink("/tmp/processor.lock");
%>
