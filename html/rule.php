<?php
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

	$begintime=time();
	require_once('config.php');

	$sec_dbsocket=sec_dbconnect();
	$REMOTE_ID=sec_usernametoid($sec_dbsocket,$REMOTE_USER);
	$APP_ID=sec_appnametoid($sec_dbsocket,'SyslogOp');
	if ( ! sec_accessallowed($sec_dbsocket,$REMOTE_ID,$APP_ID) ) {
		dbdisconnect($sec_dbsocket);
		exit;
	}
	$group=0;
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Customer');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) {$group=1; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Analyst');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=2; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Administrators');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=3; }
	$dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);

	if ( ( $group != 3 ) && ( ! userhasruleaccess ($dbsocket,$REMOTE_ID,0,$hostid) ) )  {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}
	if ( $group == 1 ) {
		$ruletype = 2;
	}

	if ( $ruletype == 3 ) {

		/* this section is for cloning hosts */

		$SQLQuery="select * from Syslog_TRule where THost_ID = $source order by TRule_ID";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows ) {
			for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$id=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_id));
				$alert=pgdatatrim($SQLQueryResultsObject->trule_logalert);
				$email=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_email));
				$expression=pgdatatrim($SQLQueryResultsObject->trule_expression);
				$desc=pgdatatrim($SQLQueryResultsObject->trule_desc);
				$startseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_startseverity));
				$stopseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_stopseverity));
				$startfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_startfacility));
				$stopfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_stopfacility));
				$ruleorlevel=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_ruleorlevel));
				$launchid=stripslashes(pgdatatrim($SQLQueryResultsObject->tlaunch_id));
				$newid=getnextid ($dbsocket, "syslog_trule_trule_id_seq");
				$threshold=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_threshold));
				$thresholdtype=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_thresholdtype));
				$starttime=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_starttime));
				$endtime=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_endtime));
				$timertype=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_timertype));
				$daysofweek=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_daysofweek));

				clonehostrule($dbsocket,$newid,$destination,$alert,$email,$expression,$desc,$startfacility,$stopfacility,$startseverity,$stopseverity,$ruleorlevel,$launchid,$threshold,$thresholdtype,$starttime,$endtime,$timertype,$daysofweek);
				if ( numdenials($dbsocket,1,$id) ) {
					clonedenials($dbsocket,$id,$newid);
				}
			}
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
		$hostid=$destination;
		$ruletype=2;
	}

	if ( $ruletype == 1 ) {
		if ( $subaction == "save" ) {
			if ( strval($id) < 1 ) {
				addpremaderule($dbsocket,$code,$desc,$typeid,$startfacility,$stopfacility,$startseverity,$stopseverity,$ruleorlevel,$launchid,$threshold,$thresholdtype);
				$id=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_ID',"TPremade_Desc='".$desc."'")));
			} else {
				updatepremaderule($dbsocket,$id,$code,$desc,$typeid,$startfacility,$stopfacility,$startseverity,$stopseverity,$ruleorlevel,$launchid,$threshold,$thresholdtype);
			}
		}
		if ( ( $action == "Add Deny Rule" ) && ( strval($id) > 0 ) ){
			addblankdenypremade($dbsocket,$id);
		}
		if ( $subaction == "savedeny" ) {
			if ( $action == "Save" ) {
				updatedenial($dbsocket,2,$denyid,$denyexp,$denystartfacility,$denystopfacility,$denystartseverity,$denystopseverity);
			}
		}
		if ( ( $id != "" ) && ( $action != "Add" ) ) {
			$SQLQuery="select * from Syslog_TPremade where TPremade_ID=$id";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
					die(pg_errormessage()."<BR>\n");
				$code=pgdatatrim($SQLQueryResultsObject->tpremade_code);
				$desc=pgdatatrim($SQLQueryResultsObject->tpremade_desc);
				$typeid=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremadetype_id));
				$ruleorlevel=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_premadeorlevel));
				$startseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_startseverity));
				$stopseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_stopseverity));
				$startfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_startfacility));
				$stopfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_stopfacility));
				$launchid=stripslashes(pgdatatrim($SQLQueryResultsObject->tlaunch_id));
				$threshold=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_threshold));
				$thresholdtype=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_thresholdtype));
			}
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		} else {
			$id="";
			$code="";
			$desc="";
		}
	}

	if ( $ruletype == 2 ) {
		if ( $action == "Save New" ) {
	                $host = gethost($dbsocket,$hostid);
			if ( $alert != 1 ) { $alert=0; }
			if ( $exptype == 2 ) {
				$cnt=count($premadeid);
				for ( $loop = 0 ; $loop != $cnt ; $loop ++ ) {
					$preid=$premadeid[($loop)];
					$expression=pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_Code',"TPremade_ID=$preid"));
					$desc=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_Desc',"TPremade_ID=$preid")));
					$ruleorlevel=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_premadeorlevel',"TPremade_ID=$preid")));
					$startseverity=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_StartSeverity',"TPremade_ID=$preid")));
					$stopseverity=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_StopSeverity',"TPremade_ID=$preid")));
					$startfacility=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_StartFacility',"TPremade_ID=$preid")));
					$stopfacility=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_Stopfacility',"TPremade_ID=$preid")));
					$launchid=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TLaunch_ID',"TPremade_ID=$preid")));
					$threshold=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_Threshold',"TPremade_ID=$preid")));
					$thresholdtype=stripslashes(pgdatatrim(relatedata ($dbsocket,'Syslog_TPremade','TPremade_ThresholdType',"TPremade_ID=$preid")));

					$starttime=mktime($starthour,$startminute,0,numberofmonth($startmonth),$startday,$startyear);
					$endtime=mktime($stophour,$stopminute,0,numberofmonth($stopmonth),$stopday,$stopyear);

					$newdaysofweek=0;
					for ( $dayloop=0; $dayloop != count($daysofweek) ; $dayloop++ ) { $newdaysofweek=$newdaysofweek+$daysofweek[$dayloop]; }
					$daysofweek=$newdaysofweek;

					addhostrule($dbsocket,$hostid,$alert,$email,$expression,$desc,$startfacility,$stopfacility,$startseverity,$stopseverity,$ruleorlevel,$launchid,$threshold,$thresholdtype,$starttime,$endtime,$timertype,$daysofweek);

				}
			} else {
				$starttime=mktime($starthour,$startminute,0,numberofmonth($startmonth),$startday,$startyear);
				$endtime=mktime($stophour,$stopminute,0,numberofmonth($stopmonth),$stopday,$stopyear);
				$newdaysofweek=0;
				for ( $dayloop=0; $dayloop != count($daysofweek) ; $dayloop++ ) { $newdaysofweek=$newdaysofweek+$daysofweek[$dayloop]; }
				$daysofweek=$newdaysofweek;
				addhostrule($dbsocket,$hostid,$alert,$email,$expression,$desc,$startfacility,$stopfacility,$startseverity,$stopseverity,$ruleorlevel,$launchid,$threshold,$thresholdtype,$starttime,$endtime,$timertype,$daysofweek);
			}
		}
		if ( ( $action == "Delete" ) && ( $subaction != "ruledeny" ) ) {
			dropruleid($dbsocket,$ruleid);
			dropdenial($dbsocket,1,$ruleid);
		}
		if ( ( $action == "Save" ) && ( $subaction != "ruledeny" ) ) {
			if ( $alert != 1 ) { $alert=0; }

				$rulestarttime=mktime($rulestarthour,$rulestartminute,0,numberofmonth($rulestartmonth),$rulestartday,$rulestartyear);
				$ruleendtime=mktime($rulestophour,$rulestopminute,0,numberofmonth($rulestopmonth),$rulestopday,$rulestopyear);
				$newdaysofweek=0;
				for ( $dayloop=0; $dayloop != count($ruledaysofweek) ; $dayloop++ ) { $newdaysofweek=$newdaysofweek+$ruledaysofweek[$dayloop]; }
				$ruledaysofweek=$newdaysofweek;
			updatehostrule($dbsocket,$ruleid,$hostid,$alert,$email,$expression,$desc,$startfacility,$stopfacility,$startseverity,$stopseverity,$ruleorlevel,$launchid,$rulethreshold,$rulethresholdtype,$rulestarttime,$ruleendtime,$ruletimertype,$ruledaysofweek);
		}
		if ( $subaction == "ruledeny" ) {
			if ( $action == "Delete" ) { dropdenial($dbsocket,1,$denyid); }
			if ( $action == "Save" ) {
				updatedenial($dbsocket,1,$denyid,$denyexp,$denystartfacility,$denystopfacility,$denystartseverity,$denystopseverity);
			}
		}
	} 

	$PageTitle="Syslog Management Tool";
	do_header($PageTitle, 'rule');
	if ( $ruletype == 1 ) {

		/* This section is for manipulating premade rules */

		if ( ( $action == "Delete" ) && ( $subaction == "savedeny" ) ) { dropdenial($dbsocket,2,$denyid); }
		if ( ( $action == "Delete" ) && ( $subaction != "savedeny" ) ) {
			if ( droppremade($dbsocket,$id) ) {
				dropdenial($dbsocket,2,$id);
				echo "Delete Successfull<BR>\n";
			} else {
				echo "Delete Failed!<BR>\n";
			}
		} else {
			if ( $startfacility == "" ) {
				$startfacility=0;
				$stopfacility=23;
				$startseverity=0;
				$stopseverity=7;
			}
			openform("rule.php","post",2,1,0);
			formfield("ruletype","Hidden",3,1,0,10,10,1);
			formfield("id","Hidden",3,1,0,10,10,$id);
			formfield("subaction","Hidden",3,1,0,10,10,"save");
	                echo "Expression:  ";
	                formfield("code","text",3,1,1,60,80,$code);
	                echo "Problem/Resolution Description:  ";
	                formfield("desc","text",3,1,1,60,256,$desc);
			echo "Premade Type:  ";
			premadetypedropdown ($dbsocket, "typeid",0,1,1,1,$typeid);
			echo "Facility Range:  ";
			facilitydropdown("startfacility",1,0,0,1,$startfacility);
			echo " to ";
			facilitydropdown("stopfacility",1,1,1,1,$stopfacility);
			echo "Severity Range:  ";
			severitydropdown("startseverity",1,0,0,1,$startseverity);
			echo " to ";
			severitydropdown("stopseverity",1,1,1,1,$stopseverity);
			echo "Rule Type:  <input type=radio name=ruleorlevel value=1 "; 
			if ( ( $ruleorlevel != "2" ) && ( $ruleorlevel != "3" ) ) { $ruleorlevel=1;} 
			if ( $ruleorlevel == 1 ) { echo " checked "; }
			echo ">Expression  ";
			echo "<input type=radio name=ruleorlevel value=3";
			if ( $ruleorlevel == 3 ) { echo " checked "; }
			echo ">Facility & Severity  ";
			echo "<input type=radio name=ruleorlevel value=2";
			if ( $ruleorlevel == 2 ) { echo " checked "; }
			echo ">Expression w/ Facility & Severity<BR>";
			echo "Launch External Program:  ";
			launchdropdown ($dbsocket, "launchid",0,1,1,1,$launchid);
			echo "Threshold Type:  <input type=radio name=thresholdtype value=0";
			if ( $thresholdtype == 0 ) { echo " checked "; }
			echo ">None  ";
			echo "<input type=radio name=thresholdtype value=1";
			if ( $thresholdtype == 1 ) { echo " checked "; }
			echo ">Supression Threshold  ";
			echo "<input type=radio name=thresholdtype value=2";
			if ( $thresholdtype == 2 ) { echo " checked "; }
			echo ">Accumulating Threshold<BR>\n";

			echo "Threshold:  ";
			thresholddropdown('threshold', 0, 0, 1, 1,$threshold);
			formsubmit("Add Deny Rule",3,1,0);
			formsubmit("Save",3,1,0);
			formreset("Reset",3,1,0);
			closeform();
			if ( numdenials($dbsocket,2,$id) ) {
				$SQLQuery="select * from Syslog_TPremadeDeny where TPremade_ID=$id order by TPremadeDeny_ID";
				$DenySQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
					die(pg_errormessage()."<BR>\n");
				$DenySQLNumRows = pg_numrows($DenySQLQueryResults);
				if ( $DenySQLNumRows ) {
					for ( $denyloop=0 ; $denyloop != $DenySQLNumRows ; $denyloop++ ) {
						$DenySQLQueryResultsObject = pg_fetch_object($DenySQLQueryResults,$denyloop) or
							die(pg_errormessage()."<BR>\n");
						$denyid=stripslashes(pgdatatrim($DenySQLQueryResultsObject->tpremadedeny_id));
						$denyexp=pgdatatrim($DenySQLQueryResultsObject->tpremadedeny_expression);
						$denystartfacility=stripslashes(pgdatatrim($DenySQLQueryResultsObject->tpremadedeny_startfacility));
						$denystopfacility=stripslashes(pgdatatrim($DenySQLQueryResultsObject->tpremadedeny_stopfacility));
						$denystartseverity=stripslashes(pgdatatrim($DenySQLQueryResultsObject->tpremadedeny_startseverity));
						$denystopseverity=stripslashes(pgdatatrim($DenySQLQueryResultsObject->tpremadedeny_stopseverity));
						echo "<TABLE BORDER=1 COLUMNS=2></TR><TD>ID:  $denyid</TD><TD></TD></TR>\n";
						openform("rule.php","post",2,1,0);
						formfield("id","Hidden",3,1,0,10,10,$id);
						formfield("denyid","Hidden",3,1,0,10,10,$denyid);
						formfield("ruletype","Hidden",3,1,0,10,10,1);
						formfield("subaction","Hidden",3,1,0,10,10,"savedeny");
						echo "<TR><TD COLSPAN=2>";
						echo "Reg. Expression Code:  ";
						formfield("denyexp","text",3,1,1,60,80,$denyexp);	
						echo "</TD></TR><TR><TD>Facility Range:  ";
						facilitydropdown("denystartfacility",1,0,0,1,$denystartfacility);
						echo " to ";
						facilitydropdown("denystopfacility",1,1,1,1,$denystopfacility);
						echo "</TD><TD>Severity Range:  ";
						severitydropdown("denystartseverity",1,0,0,1,$denystartseverity);
						echo " to ";
						severitydropdown("denystopseverity",1,1,1,1,$denystopseverity);
						echo "</TD></TR><TR><TD>";
						formsubmit("Save",3,1,0);
						formsubmit("Delete",3,1,0);
						formreset("Reset",3,1,0);
						closeform();
						echo "</TD></TR>";
						echo "</TABLE>\n";
					} 
				} 
				pg_freeresult($DenySQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
			}
		}
	}
	if ( $ruletype == 2 ) {
	
		/* This section is for adding new rules to a host */

		$host = gethost($dbsocket,$hostid);
		if ( ( $action == "Add Denial" ) && ( strval($ruleid) > 0 ) ){
			addblankdenyrule($dbsocket,$ruleid);
		}

		echo "<B>Host:  $host</B><BR>\n";
		$SQLQuery="select * from Syslog_TRule where THost_ID = $hostid order by TRule_ID";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		openform("rule.php","post",2,1,0);
		formfield("hostid","Hidden",3,1,0,10,10,$hostid);
		formfield("ruletype","Hidden",3,1,0,10,10,2);
		echo "<TABLE COLS=7 BORDER=2>\n";
		echo "<TR><TD WIDTH=120 ALIGN=CENTER VALIGN=CENTER><B>Action</B></TD><TD WIDTH=60 ALIGN=CENTER VALIGN=CENTER><B>Log Alert</B></TD><TD WIDTH=5 ALIGN=CENTER VALIGN=CENTER><B>Email Address</B></TD>" . 
			"<TD COLSPAN=2><B>Expression</B></TD><TD COLSPAN=2><B>Pre-made Rule</B></TD></TR>";
		echo "<TR><TD><input type=submit name=action value='Save New'></TD>\n<TD>" . 
			"<CENTER><input type=checkbox name=alert value=1></CENTER></TD>\n<TD><input type='Text' name='email'" . 
			" size=20 maxlength=80></TD>\n<TD><input type='radio' name='exptype' value=1 checked></TD><TD>\n" .
			"<input type='Text' name='expression' size=20 maxlength=80></TD>\n<TD>" . 
			"<input type='radio' name='exptype' value=2></TD><TD>";
		pixruledropdown ($dbsocket, "premadeid[]",2,1,0,5,"multiple");
		echo "</TD></TR>\n";
		echo "<TR><TD COLSPAN=3><B>Facility Range:</B>  ";
		facilitydropdown("startfacility",1,0,0,1,0);
		echo " <B>to</B> ";
		facilitydropdown("stopfacility",1,0,0,1,23);
		echo "</TD><TD COLSPAN=4><B>Severity Range:</B>  ";
		severitydropdown("startseverity",1,0,0,1,0);
		echo " <B>to</B> ";
		severitydropdown("stopseverity",1,0,0,1,7);
		echo "</TD></TR>"; 
		echo "<TR><TD COLSPAN=7>";
		echo "<B>Rule Type:</B>  <input type=radio name=ruleorlevel value=1 checked><B>Expression</B>  "; 
		echo "<input type=radio name=ruleorlevel value=3";
		echo "><B>Facility & Severity</B>  ";
		echo "<input type=radio name=ruleorlevel value=2";
		echo "><B>Expression w/ Facility & Severity</B></TD></TR>";

		echo "<TR><TD COLSPAN=7><B>Launch External Program:  ";
		launchdropdown ($dbsocket, "launchid",0,0,0,1,"");
		echo "</TR></TD><TR><TD COLSPAN=4 valign=center><B>Threshold Type:  <input type=radio name=thresholdtype value=0";
		if ( $thresholdtype == 0 ) { echo " checked "; }
		echo ">None  ";
		echo "<input type=radio name=thresholdtype value=1";
		if ( $thresholdtype == 1 ) { echo " checked "; }
		echo ">Supression Threshold  ";
		echo "<input type=radio name=thresholdtype value=3";
		if ( $thresholdtype == 2 ) { echo " checked "; }
		echo ">Accumulating Threshold  </B></TD><TD COLSPAN=2>";

		echo "  <B>Threshold:  </B>";
		thresholddropdown('threshold', 0, 0, 0, 1,$threshold);
		echo "</TD></TR>\n";
		echo "<TR><TD COLSPAN=7><B>Problem/Resolution Description:</B> ";
		formfield("desc","text",3,1,0,80,256,"");
		echo "</TD></TR><TR><TD COLSPAN=7><B>Rule Timer:  <input type=radio name=timertype value=0";
		if ( $timertype == 0 ) { echo " checked "; }
                echo ">None  ";
		echo "<input type=radio name=timertype value=1";
		if ( $timertype == 1 ) { echo " checked "; }
                echo ">Suspend  ";
		echo "<input type=radio name=timertype value=2";
		if ( $timertype == 2 ) { echo " checked "; }
                echo ">Delete & Suspend  ";
		echo "<input type=radio name=timertype value=3";
		if ( $timertype == 3 ) { echo " checked "; }
                echo ">Specified Suspend</B></TD></TR>\n";
		echo "<TR><TD COLSPAN=3><B>Rule Start:<BR>Time:  " ; 
		hourdropdown("starthour") ;
		echo ":" ; 
		minutedropdown("startminute") ;
		echo "<BR>\nDate:  ";
		monthdropdown("startmonth");
		echo "/";
		daydropdown("startday");
		echo "/";
		yeardropdown("startyear");
		echo "</TD><TD COLSPAN=4><B>Rule End:<BR>Time:  ";
		hourdropdown("stophour") ;
		echo ":" ; 
		minutedropdown("stopminute") ;
		echo "<BR>Date:  ";
		monthdropdown("stopmonth");
		echo "/";
		daydropdown("stopday");
		echo "/";
		yeardropdown("stopyear");
		echo "</B></TD></TR><TR><TD COLSPAN=7><B>";
		dayofweekboxes("daysofweek",0,0,0,$daysofweek) . "\n"; 
		closeform();
		echo "</B></TD></TR></TABLE><BR>\n";
		if ( $SQLNumRows ) {
			for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {

				/* This section shows rules that are already assigned to the host */

				echo "</TABLE><TABLE COLS=5 BORDER=2>\n";
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$id=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_id));
				$alert=pgdatatrim($SQLQueryResultsObject->trule_logalert);
				$email=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_email));
				$expression=pgdatatrim($SQLQueryResultsObject->trule_expression);
				$desc=pgdatatrim($SQLQueryResultsObject->trule_desc);
				$launchid=pgdatatrim($SQLQueryResultsObject->tlaunch_id);
				$startseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_startseverity));
				$stopseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_stopseverity));
				$startfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_startfacility));
				$stopfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_stopfacility));
				$ruleorlevel=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_ruleorlevel));
				$rulethreshold=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_threshold));
				$rulethresholdtype=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_thresholdtype));
				$rulestarttime=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_starttime));
				$ruleendtime=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_endtime));
				$ruletimertype=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_timertype));
				$ruledaysofweek=stripslashes(pgdatatrim($SQLQueryResultsObject->trule_daysofweek));

				if ( strval($rulestarttime) > 0 ) {
					$rulestartmonth=date("M",$rulestarttime);
					$rulestartyear=date("Y",$rulestarttime);
					$rulestartday=date("j",$rulestarttime);
					$rulestarthour=date("G",$rulestarttime);
					$rulestartminute=date("i",$rulestarttime);
				}
				if ( strval($ruleendtime) > 0 ) {
					$rulestopmonth=date("M",$ruleendtime);
					$rulestopyear=date("Y",$ruleendtime);
					$rulestopday=date("j",$ruleendtime);
					$rulestophour=date("G",$ruleendtime);
					$rulestopminute=date("i",$ruleendtime);
				}

				openform("rule.php","post",2,1,0);
				formfield("hostid","Hidden",3,1,0,10,10,$hostid);
				formfield("ruletype","Hidden",3,1,0,10,10,2);
				formfield("ruleid","Hidden",3,1,0,10,10,$id);
				echo "<TR><TD ALIGN=CENTER VALIGN=CENTER WIDTH=200>";
				echo '<input type="submit" name=action value="Save">';
				echo '<input type="submit" name=action value="Add Denial">';
				echo '<input type="submit" name=action value="Delete"></TD>';
				echo "<TD ALIGN=CENTER VALIGN=CENTER WIDTH=60><B>ID:  </B>$id</TD><TD ALIGN=CENTER VALIGN=CENTER WIDTH=110><B>Log Alert:  </B>";
				if ( $alert ) {
					echo "<input type=checkbox name=alert value=1 checked>";
				} else {
					echo "<input type=checkbox name=alert value=1>";
				}
				echo "</TD><TD WIDTH=210><B>EMail:  </B>";
				formfield("email","Text",3,1,1,20,80,$email);
				echo "</TD><TD><B>Expression:  </B>";
				formfield("expression","Text",3,1,1,20,80,$expression);
				echo "</TD></TR>"; 
				echo "<TR><TD COLSPAN=3><B>Facility Range:  </B>";
				facilitydropdown("startfacility",1,0,0,1,$startfacility);
				echo " <B>to</B> ";
				facilitydropdown("stopfacility",1,0,0,1,$stopfacility);
				echo "</TD><TD COLSPAN=4><B>Severity Range:  </B>";
				severitydropdown("startseverity",1,0,0,1,$startseverity);
				echo " <B>to</B> ";
				severitydropdown("stopseverity",1,0,0,1,$stopseverity);
				echo "</TD></TR>"; 
				echo "<TR><TD COLSPAN=7>";
				echo "<B>Rule Type:  </B><input type=radio name=ruleorlevel value=1 "; 
				if ( $ruleorlevel == 1 ) { echo " checked "; }
				echo "><B>Expression  </B>";
				echo "<input type=radio name=ruleorlevel value=3";
				if ( $ruleorlevel == 3 ) { echo " checked "; }
				echo "><B>Facility & Severity  </B>";
				echo "<input type=radio name=ruleorlevel value=2";
				if ( $ruleorlevel == 2 ) { echo " checked "; }
				echo "><B>Expression w/ Facility & Severity</B></TD></TR>";
				echo "<TR><TD COLSPAN=7><B>Launch External Program:  ";
				launchdropdown ($dbsocket, "launchid",0,0,0,1,$launchid);
				echo "</TD></TR><TR><TD COLSPAN=4><B>Threshold Type:  <input type=radio name=rulethresholdtype value=0";
				if ( $rulethresholdtype == 0 ) { echo " checked "; }
				echo ">None  ";
				echo "<input type=radio name=rulethresholdtype value=1";
				if ( $rulethresholdtype == 1 ) { echo " checked "; }
				echo ">Supression Threshold  ";
				echo "<input type=radio name=rulethresholdtype value=2";
				if ( $rulethresholdtype == 2 ) { echo " checked "; }
				echo ">Accumulating Threshold  </B>";

				echo "  </TD><TD><B>Threshold:  </B>";
				thresholddropdown('rulethreshold', 0, 0, 0, 1,$rulethreshold);
				echo "</TD></TR>\n";
				echo "<TR><TD COLSPAN=5><B>Problem/Resolution Description:  </B>";
				formfield("desc","text",3,1,0,80,256,$desc) ;

				echo "</TD></TR><TR><TD COLSPAN=7><B>Rule Timer:  <input type=radio name=ruletimertype value=0";
				if ( $ruletimertype == 0 ) { echo " checked "; }
       			        echo ">None  ";
				echo "<input type=radio name=ruletimertype value=1";
				if ( $ruletimertype == 1 ) { echo " checked "; }
       			        echo ">Suspend  ";
				echo "<input type=radio name=ruletimertype value=2";
				if ( $ruletimertype == 2 ) { echo " checked "; }
       			        echo ">Delete & Suspend  ";
				echo "<input type=radio name=ruletimertype value=3";
				if ( $ruletimertype == 3 ) { echo " checked "; }
       			        echo ">Specified Suspend</B></TD></TR>\n";
				echo "<TR><TD COLSPAN=3><B>Rule Start:<BR>Time:  ";
				hourdropdown("rulestarthour",0,0,0,1,$rulestarthour) ;
				echo ":" ; 
				minutedropdown("rulestartminute",0,0,0,1,$rulestartminute) ;
				echo "<BR>Date:  ";
				monthdropdown("rulestartmonth",0,0,0,1,$rulestartmonth);
				echo "/";
				daydropdown("rulestartday",0,0,0,1,$rulestartday);
				echo "/";
				yeardropdown("rulestartyear",0,0,0,1,$rulestartyear);
				echo "</TD><TD COLSPAN=4><B>Rule End:<BR>Time:  ";
				hourdropdown("rulestophour",0,0,0,1,$rulestophour) ;
				echo ":" ; 
				minutedropdown("rulestopminute",0,0,0,1,$rulestopminute) ;
				echo "<BR>Date:  ";
				monthdropdown("rulestopmonth",0,0,0,1,$rulestopmonth);
				echo "/";
				daydropdown("rulestopday",0,0,0,1,$rulestopday);
				echo "/";
				yeardropdown("rulestopyear",0,0,0,1,$rulestopyear);
				echo "</B></TD></TR><TR><TD COLSPAN=7><B>";
				dayofweekboxes("ruledaysofweek",0,0,0,$ruledaysofweek) . "\n"; 
				closeform();
				echo "</B></TD></TR>\n";
				echo "</TABLE>\n";

				if ( numdenials($dbsocket,1,$id) ) {

					/* This section is for handling denial rules */

					$SQLQuery="select * from Syslog_TRuleDeny where TRule_ID=$id order by TRuleDeny_ID" ;
					$DenySQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
						die(pg_errormessage()."<BR>\n");
					$DenySQLNumRows = pg_numrows($DenySQLQueryResults);
					if ( $DenySQLNumRows ) {
						for ( $denyloop=0 ; $denyloop != $DenySQLNumRows ; $denyloop++ ) {
							$DenySQLQueryResultsObject = pg_fetch_object($DenySQLQueryResults,$denyloop) or
								die(pg_errormessage()."<BR>\n");
							$denyid=stripslashes(pgdatatrim($DenySQLQueryResultsObject->truledeny_id));
							$denyexp=pgdatatrim($DenySQLQueryResultsObject->truledeny_expression);
							$denystartfacility=stripslashes(pgdatatrim($DenySQLQueryResultsObject->truledeny_startfacility));
							$denystopfacility=stripslashes(pgdatatrim($DenySQLQueryResultsObject->truledeny_stopfacility));
							$denystartseverity=stripslashes(pgdatatrim($DenySQLQueryResultsObject->truledeny_startseverity));
							$denystopseverity=stripslashes(pgdatatrim($DenySQLQueryResultsObject->truledeny_stopseverity));
							echo "<TABLE BORDER=1 COLUMNS=2 BGCOLOR=#BBBBBB></TR><TD><B><FONT COLOR=#FF0000>DENIAL ID:  $denyid </B></FONT></TD><TD ALIGN=RIGHT>";
							openform("rule.php","post",2,0,0);
							formsubmit("Save",3,1,0);
							formsubmit("Delete",3,1,0);
							formreset("Reset",3,1,0);
							echo "</TD></TR>\n";
							formfield("denyid","Hidden",3,1,0,10,10,$denyid);
							formfield("hostid","Hidden",3,1,0,10,10,$hostid);
							formfield("ruletype","Hidden",3,1,0,10,10,2);
							formfield("ruleid","Hidden",3,1,0,10,10,$id);
							formfield("subaction","Hidden",3,1,0,10,10,"ruledeny");
							echo "<TR><TD COLSPAN=2>";
							echo "Expression:  ";
							formfield("denyexp","text",3,1,1,60,80,$denyexp);
							echo "</TD></TR><TR><TD>Facility Range:  ";
							facilitydropdown("denystartfacility",1,0,0,1,$denystartfacility);
							echo " to ";
							facilitydropdown("denystopfacility",1,1,1,1,$denystopfacility);
							echo "</TD><TD>Severity Range:  ";
							severitydropdown("denystartseverity",1,0,0,1,$denystartseverity);
							echo " to ";
							severitydropdown("denystopseverity",1,1,1,1,$denystopseverity);
							echo "</TD></TR>";
							closeform();
							echo "</TD></TR>";
							echo "</TABLE>\n";
						}
						echo "<BR>\n";
					}
					pg_freeresult($DenySQLQueryResults) or
						die(pg_errormessage() . "<BR>\n");
				}
			}
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
