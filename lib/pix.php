<%
/*=============================================================================
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

/********************************************************************/
/*                                                                  */
/*  File:  pix.php                                                  */
/*  Purpose:  Provide the majority of functions used by the Syslog  */
/*            Management Tool                                       */
/*                                                                  */
/********************************************************************/

/********************************************************************/
require_once('pgsql.php');

/********************************************************************/
define("SMACDB", "TSyslog");			/* Username used to access the DB */
define("SMACPASS", "N88iqueU");
define("WARNINGADDRESS", "cscgiss1@maybank.com.sg"); /* Email address that SMT uses as the target to get warnings and misc. reports */
define("SMTVER","1.00");			/* The version of the software that the user sees */
define("LEFTWIDTH","150");			/* Control the width of the left panel called by index.php */

function dayofweekboxes($fieldname, $tabs=0, $cr=0, $br=0, $selected="") {

	echo tabs($tabs); 
	if ( $selected >= 64 ) {
		$sunday=1;
		$selected=$selected - 64;
	}
	if ( $selected >= 32 ) {
		$monday=1;
		$selected=$selected - 32;
	}
	if ( $selected >= 16 ) {
		$tuesday=1;
		$selected=$selected - 16;
	}
	if ( $selected >= 8 ) {
		$wednesday=1;
		$selected=$selected - 8;
	}
	if ( $selected >= 4 ) {
		$thursday=1;
		$selected=$selected - 4;
	}
	if ( $selected >= 2 ) {
		$friday=1;
		$selected=$selected - 2;
	}
	if ( $selected >= 1 ) {
		$saturday=1;
		$selected=$selected - 1;
	}
	echo "<input type=checkbox name=$fieldname" . '[]' . " value=64";
	if ( $sunday ) { echo " checked "; }
	echo ">Sunday  ";
	echo "<input type=checkbox name=$fieldname" . '[]' . " value=32";
	if ( $monday ) { echo " checked "; }
	echo ">Monday  ";
	echo "<input type=checkbox name=$fieldname" . '[]' . " value=16";
	if ( $tuesday ) { echo " checked "; }
	echo ">Tuesday  ";
	echo "<input type=checkbox name=$fieldname" . '[]' . " value=8";
	if ( $wednesday ) { echo " checked "; }
	echo ">Wednesday  ";
	echo "<input type=checkbox name=$fieldname" . '[]' . " value=4";
	if ( $thursday ) { echo " checked "; }
	echo ">Thursday  ";
	echo "<input type=checkbox name=$fieldname" . '[]' . " value=2";
	if ( $friday ) { echo " checked "; }
	echo ">Friday  ";
	echo "<input type=checkbox name=$fieldname" . '[]' . " value=1";
	if ( $saturday ) { echo " checked "; }
	echo ">Saturday  ";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  supressruleresults                                    */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  determine if a rule is between the date & time     */
/*    specified for the rule type                                   */
/*                                                                  */
/********************************************************************/
function supressruleresults($starttime,$endtime,$daysofweek,$timertype,$timestamp) {

	$Results=0;
	$sunday=0; /* 1 */
	$monday=0; /* 2 */
	$tuesday=0; /* 4 */
	$wednesday=0; /* 8 */
	$thursday=0; /* 16 */
	$friday=0; /* 32 */
	$saturday=0; /* 64 */
	$day=date("D",$timestamp);


	if ( ( $timertype == 1 ) || ( $timertype == 2 ) ) {
		if ( ( $timestamp >= $starttime ) && ( $timestamp <= $endtime ) && ( $starttime < $endtime ) ) {
			$Results=1;
		} 
		if ( ( ( $timestamp >= $starttime ) || ( $timestamp <= $endtime ) ) && ( $starttime > $endtime ) ) {
			$Results=1;
		} 
	}
	if ( $timertype == 3 ) {
		if ( $daysofweek >= 64 ) {
			$sunday=1;
			$daysofweek=$daysofweek - 64;
		}
		if ( $daysofweek >= 32 ) {
			$monday=1;
			$daysofweek=$daysofweek - 32;
		}
		if ( $daysofweek >= 16 ) {
			$tuesday=1;
			$daysofweek=$daysofweek - 16;
		}
		if ( $daysofweek >= 8 ) {
			$wednesday=1;
			$daysofweek=$daysofweek - 8;
		}
		if ( $daysofweek >= 4 ) {
			$thursday=1;
			$daysofweek=$daysofweek - 4;
		}
		if ( $daysofweek >= 2 ) {
			$friday=1;
			$daysofweek=$daysofweek - 2;
		}
		if ( $daysofweek >= 1 ) {
			$saturday=1;
			$daysofweek=$daysofweek - 1;
		}

		/* convert from hh:mm mm/dd/yyyy to seconds since 1970 */
		$starthour=date("G",$starttime);
		$startminute=date("i",$starttime);
		$stophour=date("G",$endtime);
		$stopminute=date("i",$endtime);
		$hour=date("G",$timestamp);
		$minute=date("i",$timestamp);

		/* convert time to sec since 1970 since it provides easy date/time conversion */
		$starttime=mktime($starthour,$startminute,0,1,1,2003);
		$endtime=mktime($stophour,$stopminute,0,1,1,2003);
		$timestamp=mktime($hour,$minute,0,1,1,2003);

		if ( ( $sunday ) && ( $day == "Sun" ) ) {
			if ( ( $timestamp >= $starttime ) && ( $timestamp <= $endtime ) && ( $starttime < $endtime ) ) {
				$Results=1;
			} 
			if ( ( ( $timestamp >= $starttime ) || ( $timestamp <= $endtime ) ) && ( $starttime > $endtime ) ) {
				$Results=1;
			} 
		} 
		if ( ( $monday ) && ( $day == "Mon" ) ) {
			if ( ( $timestamp >= $starttime ) && ( $timestamp <= $endtime ) && ( $starttime < $endtime ) ) {
				$Results=1;
			} 
			if ( ( ( $timestamp >= $starttime ) || ( $timestamp <= $endtime ) ) && ( $starttime > $endtime ) ) {
				$Results=1;
			} 
		} 
		if ( ( $tuesday ) && ( $day == "Tue" ) ) {
			if ( ( $timestamp >= $starttime ) && ( $timestamp <= $endtime ) && ( $starttime < $endtime ) ) {
				$Results=1;
			} 
			if ( ( ( $timestamp >= $starttime ) || ( $timestamp <= $endtime ) ) && ( $starttime > $endtime ) ) {
				$Results=1;
			} 
		} 
		if ( ( $wednesday ) && ( $day == "Wed" ) ) {
			if ( ( $timestamp >= $starttime ) && ( $timestamp <= $endtime ) && ( $starttime < $endtime ) ) {
				$Results=1;
			} 
			if ( ( ( $timestamp >= $starttime ) || ( $timestamp <= $endtime ) ) && ( $starttime > $endtime ) ) {
				$Results=1;
			} 
		} 
		if ( ( $thursday ) && ( $day == "Thu" ) ) {
			if ( ( $timestamp >= $starttime ) && ( $timestamp <= $endtime ) && ( $starttime < $endtime ) ) {
				$Results=1;
			} 
			if ( ( ( $timestamp >= $starttime ) || ( $timestamp <= $endtime ) ) && ( $starttime > $endtime ) ) {
				$Results=1;
			} 
		} 
		if ( ( $friday ) && ( $day == "Fri" ) ) {
			if ( ( $timestamp >= $starttime ) && ( $timestamp <= $endtime ) && ( $starttime < $endtime ) ) {
				$Results=1;
			} 
			if ( ( ( $timestamp >= $starttime ) || ( $timestamp <= $endtime ) ) && ( $starttime > $endtime ) ) {
				$Results=1;
			} 
		} 
		if ( ( $saturday ) && ( $day == "Sat" ) ) {
			if ( ( $timestamp >= $starttime ) && ( $timestamp <= $endtime ) && ( $starttime < $endtime ) ) {
				$Results=1;
			} 
			if ( ( ( $timestamp >= $starttime ) || ( $timestamp <= $endtime ) ) && ( $starttime > $endtime ) ) {
				$Results=1;
			} 
		} 
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  thresholddropdown                                     */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  HTML control to create a drop down box listing     */
/*               rule threshold values                              */
/*                                                                  */
/********************************************************************/
function thresholddropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1,$selected="") {
	
	$values=array(0,1,2,3,4,5,10,15,20,25,30,35,40,45,50,60,70,80,90,100,200,300,400,500,600,700,800,900,1000,2000,3000,4000,5000,6000,7000,8000,9000,10000,20000,30000,40000,50000);
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	for ( $loop=0 ; $loop != count($values) ; $loop++ ) {
		echo tabs($tabs+1) . "<option value=$values[$loop]";
		if ( $selected == $values[$loop] ) { echo " selected"; } 
		if ( $loop == 0 ) {
			echo ">Never</option>\n";
		}
		if ( $loop == 1 ) {
			echo ">$values[$loop] hit</option>\n";
		}
		if ( $loop > 1 ) {
			echo ">$values[$loop] hits</option>\n";
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}


/********************************************************************/
/*                                                                  */
/* Function:  clearlaunchqueue                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Delete Syslog_TLaunchQueue records w/ appropriate  */
/*               ID for either stale connections or properly        */
/*               closing out a processor                            */
/*                                                                  */
/********************************************************************/
function clearlaunchqueue($dbsocket,$mailid) {

	$SQLQuery="begin;delete from Syslog_TLaunchQueue where TMail_ID=$mailid;commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);	
}

/********************************************************************/
/*                                                                  */
/* Function:  updatelaunch                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update a launch entry in the Syslog_TLaunch table  */
/*                                                                  */
/********************************************************************/
function updatelaunch($dbsocket,$launchid,$shortdesc,$longdesc,$program) {

	$Results=0;
	$shortdesc=fixappostrophe(stripslashes(pgdatatrim($shortdesc)));
	$longdesc=fixappostrophe(stripslashes(pgdatatrim($longdesc)));
	$program=fixappostrophe(stripslashes(pgdatatrim($program)));
	$launchid=fixappostrophe(stripslashes(pgdatatrim($launchid)));
	if ( ( $shortdesc != "" ) && ( $program != "" ) ) {
		$SQLQuery = "begin;update syslog_tlaunch set tlaunch_shortdesc='$shortdesc',tlaunch_longdesc='$longdesc',tlaunch_program='$program' where tlaunch_id=$launchid;commit;"; 
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  addlaunchdataentry                                    */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Adds entries to Syslog_TLaunchQueue table for      */
/*               queuing up date for an external program            */
/*                                                                  */
/********************************************************************/
function addlaunchdataentry($dbsocket,$launchid,$id,$mailid,$desc) {

	$Results=0;
	$launchid=fixappostrophe(stripslashes(pgdatatrim($launchid)));
	$id=fixappostrophe(stripslashes(pgdatatrim($id)));
	$mailid=fixappostrophe(stripslashes(pgdatatrim($mailid)));
	$desc=fixappostrophe(stripslashes(pgdatatrim($desc)));
	if ( ( $launchid != "" ) && ( $id != "" ) ) {
		$SQLQuery = "begin;insert into Syslog_TLaunchQueue (TLaunchQueue_Desc,TLaunch_ID,TMail_ID,TSyslog_ID) values ('$desc',$launchid,$mailid,$id);commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  launchassociated                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Return whether or not there is an association for  */
/*               a given external program.                          */
/*                                                                  */
/********************************************************************/
function launchassociated($dbsocket,$launchid,$id,$mailid) {

	$Results=0;
	$launchid=fixappostrophe(stripslashes(pgdatatrim($launchid)));
	$id=fixappostrophe(stripslashes(pgdatatrim($id)));
	$mailid=fixappostrophe(stripslashes(pgdatatrim($mailid)));
	if ( ( $launchid != "" ) && ( $id != "" ) ) {
		$SQLQuery = "select * from Syslog_TLaunchQueue where tlaunch_id=$launchid and TSyslog_ID=$id and TMail_ID=$mailid";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) {
			$Results = pg_numrows($SQLQueryResults);
		}
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  droplaunch                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Drop a launchable program entry from Syslog_...    */
/*               Launch table for a given TLaunch_ID                */
/*                                                                  */
/********************************************************************/
function droplaunch($dbsocket,$launchid) {

	$Results=0;
	$launchid=fixappostrophe(stripslashes(pgdatatrim($launchid)));
	if ( $launchid != "" ) {
		$SQLQuery = "begin;delete from syslog_tlaunch where tlaunch_id=$launchid;commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  addlaunch                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Add an launch program entry in the Syslog_....     */
/*               TLaunch table                                      */
/*                                                                  */
/********************************************************************/
function addlaunch($dbsocket,$shortdesc, $longdesc, $program) {

	$Results=0;
	$shortdesc=fixappostrophe(stripslashes(pgdatatrim($shortdesc)));
	$longdesc=fixappostrophe(stripslashes(pgdatatrim($longdesc)));
	$program=fixappostrophe(stripslashes(pgdatatrim($program)));
	if ( ( $shortdesc != "" ) && ( $program != "" ) ) {
		$SQLQuery = "begin;insert into syslog_tlaunch (tlaunch_shortdesc, tlaunch_longdesc, tlaunch_program) values ('$shortdesc','$longdesc','$program');commit;"; 
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  launchdropdown                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  An HTML control for providing a launch drop down   */
/*               box                                                */
/*                                                                  */
/********************************************************************/
function launchdropdown ($dbsocket, $fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="",$listnone=1) {

	$SQLQuery="select * from Syslog_TLaunch order by TLaunch_ShortDesc";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	if ( $listnone ) { 
		echo "<option value=0>None</option>\n";
	}
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$desc=stripslashes(pgdatatrim($SQLQueryResultsObject->tlaunch_shortdesc));
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tlaunch_id));
			if ( $id==$selected ) {
				echo tabs($tabs+1) . "<option value=$id selected>$desc</option>\n";
			} else {
				echo tabs($tabs+1) . "<option value=$id>$desc</option>\n";
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
	die(pg_errormessage() . "<BR>\n");
}


/********************************************************************/
/*                                                                  */
/* Function:  reporttypename                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Easily convert report types to report names        */
/*                                                                  */
/********************************************************************/
function reporttypename($value=0) {

        switch ($value) {
                case 1:
			$Results="Log Volume By Severity";
                        break;
                case 2:
			$Results="Log Volume By Facility";
                        break;
                case 3:
			$Results="Cisco Pix:  Bandwidth Breakdown";
                        break;
		case 4:
			$Results="Cisco VPN Usage Report";
			break;
		default:
			$Results="Log Volume By Severity";
        }
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  reporttypedropdown                                    */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  HTML control to create a report type drop down     */
/*               control                                            */
/*                                                                  */
/********************************************************************/
function reporttypedropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1,$selected="") {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	echo tabs($tabs+1) . "<option value=1";
	if ( $selected == 1 ) { echo " selected"; }
	echo ">Log Volume By Severity</option>\n";
	echo tabs($tabs+1) . "<option value=2";
	if ( $selected == 2 ) { echo " selected"; }
	echo ">Log Volume By Facility</option>\n";
	echo tabs($tabs+1) . "<option value=3";
	if ( $selected == 3 ) { echo " selected"; }
	echo ">Cisco Pix:  Bandwidth Breakdown</option>\n";
	echo tabs($tabs+1) . "<option value=4";
	if ( $selected == 4 ) { echo " selected"; }
	echo ">Cisco VPN Usage Report</option>\n";
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  startbody                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Provides an easy control to change the basic feel  */
/*               of SMT                                             */
/*                                                                  */
/********************************************************************/
function startbody($tabs=0) {

	echo tabs($tabs) . "<BODY bgcolor='#FFFFFF' text='#000000' LINK='#336699' VLINK='#9900FF' ALINK='#CC9933' background='images/tile.gif'><basefont size=4>";
}	

/********************************************************************/
/*                                                                  */
/* Function:  drophostrules                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Delete syslog & deny rules for a given host_id     */
/*                                                                  */
/********************************************************************/
function drophostrules($dbsocket,$hostid) {
	
	$Results=0;
	if ( strval($hostid) > 0 ) {
		$SQLQuery="begin; delete from syslog_truledeny where ( syslog_trule.thost_id=$hostid and syslog_trule.trule_id=syslog_truledeny.trule_id) ; delete from syslog_trule where syslog_trule.thost_id=$hostid; commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  drophostsyslogs                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Remove syslogs from the database for a given host  */
/*                                                                  */
/********************************************************************/
function drophostsyslogs($dbsocket,$hostid) {

	$Results=0;
	$host=gethost($dbsocket,$hostid);
	if ( strval($hostid) > 0 ) {
		$SQLQuery="begin;delete from TSyslog where host='$host';commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  drophostalerts                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Remove alerts for a given host                     */
/*                                                                  */
/********************************************************************/
function drophostalerts($dbsocket,$hostid) {

	$Results=0;
	if ( strval($hostid) > 0 ) {
		$SQLQuery="begin;delete from Syslog_TAlert where TSyslog.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=$hostid and Syslog_TAlert.TSyslog_id=TSyslog.TSyslog_id; delete from Syslog_TAlert where Syslog_TArchive.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=$hostid and Syslog_TAlert.TSyslog_id=Syslog_TArchive.TSyslog_id;commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  drophostarchivesyslogs                                */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Remove syslogs from the archive table for a given  */ 
/* host                                                             */
/*                                                                  */
/********************************************************************/
function drophostarchivesyslogs($dbsocket,$hostid) {

	$Results=0;
	$host=gethost($dbsocket,$hostid);
	if ( strval($hostid) > 0 ) {
		$SQLQuery="begin;delete from Syslog_TArchive where host='$host';commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  drophostcustprof                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Remove a host from a customer profile              */
/*                                                                  */
/********************************************************************/
function drophostcustprof($dbsocket,$hostid) {

	$Results=0;
	if ( strval($hostid) > 0 ) {
		$SQLQuery="begin;delete from syslog_tcustomerprofile where thost_id=$hostid;commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  drophostprocprof                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Remove a host from a processor profile             */
/*                                                                  */
/********************************************************************/
function drophostprocprof($dbsocket,$hostid) {

	$Results=0;
	if ( strval($hostid) > 0 ) {
		$SQLQuery="begin;delete from syslog_tprocessorprofile where thost_id=$hostid;commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  renamehosts                                           */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Provide a generic function to rename hosts in any  */
/*               given table                                        */
/*                                                                  */
/********************************************************************/
function renamehosts($dbsocket,$tablename,$expression,$fieldname,$hostname) {
         
	$SQLNumRows = 0;
	$tablename=stripslashes(pgdatatrim($tablename));
	$expression=stripslashes(pgdatatrim($expression));
	$fieldname=stripslashes(pgdatatrim($fieldname));
	$hostname=stripslashes(pgdatatrim($hostname));
	if ( $hostname != "" ) {
		$SQLQuery="begin;update $tablename set $fieldname='$hostname' where $expression;commit;";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  withinbounds                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Determine if a given severity & facilty exists     */
/*               between the supplied ranges                        */
/*                                                                  */
/********************************************************************/
function withinbounds($facility,$severity,$startfacility,$stopfacility,$startseverity,$stopseverity) {

	$Results = 0;
	if ( ( $facility >= $startfacility ) && ( $facility <= $stopfacility ) && 
		( $severity >= $startseverity ) && ( $severity <= $stopseverity ) ) {
		$Results=1;
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  facilitydropdown                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a drop down box of    */
/*               Syslog Facilities                                  */
/*                                                                  */
/********************************************************************/
function facilitydropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1,$selected="") {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	echo tabs($tabs+1) . "<option value=0";
	if ( $selected == 0 ) { echo " selected"; }
	echo ">kernel</option>\n";
	echo tabs($tabs+1) . "<option value=1";
	if ( $selected == 1 ) { echo " selected"; }
        echo ">random</option>\n";
	echo tabs($tabs+1) . "<option value=2";
	if ( $selected == 2 ) { echo " selected"; }
        echo ">mail</option>\n";
	echo tabs($tabs+1) . "<option value=3";
	if ( $selected == 3 ) { echo " selected"; }
        echo ">daemon</option>\n";
	echo tabs($tabs+1) . "<option value=4";
	if ( $selected == 4 ) { echo " selected"; }
        echo ">auth</option>\n";
	echo tabs($tabs+1) . "<option value=5";
	if ( $selected == 5 ) { echo " selected"; }
        echo ">msyslog</option>\n";
	echo tabs($tabs+1) . "<option value=6";
	if ( $selected == 6 ) { echo " selected"; }
        echo ">lpr</option>\n";
	echo tabs($tabs+1) . "<option value=7";
	if ( $selected == 7 ) { echo " selected"; }
        echo ">news</option>\n";
	echo tabs($tabs+1) . "<option value=8";
	if ( $selected == 8 ) { echo " selected"; }
        echo ">uucp</option>\n";
	echo tabs($tabs+1) . "<option value=9";
	if ( $selected == 9 ) { echo " selected"; }
        echo ">cron</option>\n";
	echo tabs($tabs+1) . "<option value=10";
	if ( $selected == 10 ) { echo " selected"; }
        echo ">authpriv</option>\n";
	echo tabs($tabs+1) . "<option value=11";
	if ( $selected == 11 ) { echo " selected"; }
        echo ">ftp</option>\n";
	echo tabs($tabs+1) . "<option value=16";
	if ( $selected == 16 ) { echo " selected"; }
        echo ">local0</option>\n";
	echo tabs($tabs+1) . "<option value=17";
	if ( $selected == 17 ) { echo " selected"; }
        echo ">local1</option>\n";
	echo tabs($tabs+1) . "<option value=18";
	if ( $selected == 18 ) { echo " selected"; }
        echo ">local2</option>\n";
	echo tabs($tabs+1) . "<option value=19";
	if ( $selected == 19 ) { echo " selected"; }
        echo ">local3</option>\n";
	echo tabs($tabs+1) . "<option value=20";
	if ( $selected == 20 ) { echo " selected"; }
        echo ">local4</option>\n";
	echo tabs($tabs+1) . "<option value=21";
	if ( $selected == 21 ) { echo " selected"; }
        echo ">local5</option>\n";
	echo tabs($tabs+1) . "<option value=22";
	if ( $selected == 22 ) { echo " selected"; }
        echo ">local6</option>\n";
	echo tabs($tabs+1) . "<option value=23";
	if ( $selected == 23 ) { echo " selected"; }
        echo ">local7</option>\n";
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  severitydropdown                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a drop down box of    */
/*               Syslog Severities                                  */
/*                                                                  */
/********************************************************************/
function severitydropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1,$selected="") {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	echo tabs($tabs+1) . "<option value=0";
	if ( $selected == 0 ) { echo " selected"; }
	echo ">emergency</option>\n";
	echo tabs($tabs+1) . "<option value=1";
	if ( $selected == 1 ) { echo " selected"; }
	echo ">alerts</option>\n";
	echo tabs($tabs+1) . "<option value=2";
	if ( $selected == 2 ) { echo " selected"; }
	echo ">critical</option>\n";
	echo tabs($tabs+1) . "<option value=3";
	if ( $selected == 3 ) { echo " selected"; }
	echo ">errors</option>\n";
	echo tabs($tabs+1) . "<option value=4";
	if ( $selected == 4 ) { echo " selected"; }
	echo ">warnings</option>\n";
	echo tabs($tabs+1) . "<option value=5";
	if ( $selected == 5 ) { echo " selected"; }
	echo ">notifications</option>\n";
	echo tabs($tabs+1) . "<option value=6";
	if ( $selected == 6 ) { echo " selected"; }
	echo ">informational</option>\n";
	echo tabs($tabs+1) . "<option value=7";
	if ( $selected == 7 ) { echo " selected"; }
	echo ">debug</option>\n";
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  verbosefacility                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Return the english desc for a given facility       */
/*                                                                  */
/********************************************************************/
function verbosefacility($facility) {

	$Results="";
	if ( $facility == "" ) { $facility=24; }
	if ( $facility == "0" ) { $Results="kernel"; }
	if ( $facility == "1" ) { $Results="random"; }
	if ( $facility == "2" ) { $Results="mail"; }
	if ( $facility == "3" ) { $Results="daemon"; }
	if ( $facility == "4" ) { $Results="auth"; }
	if ( $facility == "5" ) { $Results="msyslog"; }
	if ( $facility == "6" ) { $Results="lpr"; }
	if ( $facility == "7" ) { $Results="news"; }
	if ( $facility == "8" ) { $Results="uucp"; }
	if ( $facility == "9" ) { $Results="cron"; }
	if ( $facility == "10" ) { $Results="authpriv"; }
	if ( $facility == "11" ) { $Results="ftp"; }
	if ( $facility == "16" ) { $Results="local0"; }
	if ( $facility == "17" ) { $Results="local1"; }
	if ( $facility == "18" ) { $Results="local2"; }
	if ( $facility == "19" ) { $Results="local3"; }
	if ( $facility == "20" ) { $Results="local4"; }
	if ( $facility == "21" ) { $Results="local5"; }
	if ( $facility == "22" ) { $Results="local6"; }
	if ( $facility == "23" ) { $Results="local7"; }
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  verboseseverity                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Return the english desc for a given severity       */
/*                                                                  */
/********************************************************************/
function verboseseverity($severity) {

	$Results="";
	if ( $severity == "" ) { $severity=7; }
	if ( $severity == 0 ) { $Results="emergency"; } 
	if ( $severity == 1 ) { $Results="alerts"; } 
	if ( $severity == 2 ) { $Results="critical"; } 
	if ( $severity == 3 ) { $Results="errors"; } 
	if ( $severity == 4 ) { $Results="warnings"; } 
	if ( $severity == 5 ) { $Results="notifications"; } 
	if ( $severity == 6 ) { $Results="informational"; } 
	if ( $severity == 7 ) { $Results="debug"; } 
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  cleanarchives                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  This function is used to help clean up after a     */
/*               stale processor.  Since we cannot have duplicate   */
/*               log IDs, we must be sure to delete old logs that   */
/*               might be left over.  We delete all log with a      */
/*               syslog_id > then the last time we processed        */
/*                                                                  */
/********************************************************************/
function cleanarchives($dbsocket,$cleanid,$cleanhost) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TArchive where TSyslog_ID > $cleanid and host='$cleanhost';commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  cleanalerts                                           */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  This function is used to help clean up after a     */
/*               stale processor.  Since we cannot have duplicate   */
/*               alerts, we must be sure to delete old alerts that  */
/*               might be left over.  We delete all alerts with a   */
/*               syslog_id > then the last time we processed        */
/*                                                                  */
/********************************************************************/
function cleanalerts($dbsocket,$cleanid,$cleanhost) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TAlert where Syslog_TAlert.TSyslog_ID=TSyslog.TSyslog_ID and TSyslog.TSyslog_ID > $cleanid and TSyslog.host='$cleanhost';commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  dropfilterdata                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Drop filter row that has the appropriate           */
/*               filterdata_id                                      */
/*                                                                  */
/********************************************************************/
function dropfilterdata($dbsocket,$filterdataid) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TFilterData where TFilterData_ID=$filterdataid;commit;"; 
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  updatefilterdata                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update a given filterdata row                      */
/*                                                                  */
/********************************************************************/
function updatefilterdata($dbsocket,$filterid,$filter,$include,$filterorlevel,$startfacility,$stopfacility,$startseverity,$stopseverity) {

	$userorglobal=strval($userorglobal);
	$SQLQuery="begin;update Syslog_TFilterData set TFilterData_Include=$include,TFilterData_Filter='$filter',TFilterData_FilterOrLevel=$filterorlevel,TFilterData_StartFacility=$startfacility,TFilterData_StopFacility=$stopfacility,TFilterData_StartSeverity=$startseverity,TFilterData_StopSeverity=$stopseverity where TFilterData_ID=$filterid;commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	$Results=$SQLNumRows;
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  updatefilter                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update a given filter row                          */
/*                                                                  */
/********************************************************************/
function updatefilter($dbsocket,$filterid,$filterdesc,$userorglobal) {

	$filterdesc=pgdatatrim(fixappostrophe($filterdesc));
	$userorglobal=strval($userorglobal);
	$SQLQuery="begin;update Syslog_TFilter set TFilter_UserOrGlobal=$userorglobal,TFilter_Desc='$filterdesc' where TFilter_ID=$filterid;commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	$Results=$SQLNumRows;
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  dropfilter                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Delete a given filter row                          */
/*                                                                  */
/********************************************************************/
function dropfilter($dbsocket,$filterid) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TFilter where TFilter_ID=$filterid;commit;"; 
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  dropallfilterdata                                     */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Delete all assocated filterdata rows for a given   */
/*               filter_id                                          */
/*                                                                  */
/********************************************************************/
function dropallfilterdata($dbsocket,$filterid) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TFilterData where TFilter_ID=$filterid;commit;"; 
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  dropprocessorhostfromprofile                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Delete an associated processor-host profile entry  */
/*                                                                  */
/********************************************************************/
function dropprocessorhostfromprofile($dbsocket,$id) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TProcessorProfile where THost_ID=$id;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  dropprocessorprofile                                  */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Delete an associated processor-host profile entry  */
/*                                                                  */
/********************************************************************/
function dropprocessorprofile($dbsocket,$id) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TProcessorProfile where TProcessorProfile_ID=$id;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  addblankdenyrule                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  create an empty deny rule for editing              */
/*                                                                  */
/********************************************************************/
function addblankdenyrule($dbsocket,$id) {

	$SQLQuery="begin;insert into Syslog_TRuleDeny (TRule_ID,TRuleDeny_Expression,TRuleDeny_StartFacility,TRuleDeny_StopFacility,TRuleDeny_StartSeverity,TRuleDeny_StopSeverity) values ($id,'',0,23,0,7);commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  addblankdenyrulepremade                               */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  create an empty premade deny rule for editing      */
/*                                                                  */
/********************************************************************/
function addblankdenypremade($dbsocket,$id) {

	$SQLQuery="begin;insert into Syslog_TPremadeDeny (TPremade_ID,TPremadeDeny_Expression,TPremadeDeny_StartFacility,TPremadeDeny_StopFacility,TPremadeDeny_StartSeverity,TPremadeDeny_StopSeverity) values ($id,'',0,23,0,7);commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  addprocessorprofile                                   */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  add a record to associate a host to a processor    */
/*                                                                  */
/********************************************************************/
function addprocessorprofile($dbsocket,$userid,$hostid) {

	$SQLQuery="begin;insert into Syslog_TProcessorProfile (THost_ID,TLogin_ID) values ($hostid,$userid);commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  assignedtoprocessor                                   */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  check to see if a host is assigned to a processor  */
/*                                                                  */
/********************************************************************/
function assignedtoprocessor ($dbsocket,$hostid) {

	$SQLQuery="select thost_id from Syslog_TProcessorProfile where THost_ID=$hostid";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  assignedtouser                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  check to see if a user has assigned hosts          */
/*                                                                  */
/********************************************************************/
function assignedtouser ($dbsocket,$userid,$hostid) {

	$SQLQuery="select tcustomerprofile_id,TLogin_ID,THost_ID from Syslog_TCustomerProfile where THost_ID=$hostid and TLogin_ID=$userid";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  userhasruleaccess                                     */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  check to see if a user has permission to edit any  */
/*               hosts rules or a specific host's rules             */
/*                                                                  */
/********************************************************************/
function userhasruleaccess ($dbsocket,$userid,$allhosts=1,$hostid=0) {

        $SQLQuery="select tcustomerprofile_id from Syslog_TCustomerProfile where TLogin_ID=$userid and TCustomerProfile_EditRules = 1";
	if ( ( ! $allhosts ) && ( $hostid !=0 ) ) {
		$SQLQuery = $SQLQuery . " and THost_ID=$hostid";
	} 
        $SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
                die(pg_errormessage()."<BR>\n");
        $SQLNumRows = pg_numrows($SQLQueryResults);
        pg_freeresult($SQLQueryResults) or
                die(pg_errormessage() . "<BR>\n");
        return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  idexist                                               */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Provide a table independent function to see if an  */
/*               ID exists for the supplied table                   */
/*                                                                  */
/********************************************************************/
function idexist($dbsocket,$tablename,$idname,$id) {

	$SQLNumRows = 0;
	$tablename=stripslashes(pgdatatrim($tablename));
	$idname=stripslashes(pgdatatrim($idname));
	$id=stripslashes(pgdatatrim($id));
	if ( ( is_string($idname) ) && ( is_string($tablename) ) && ( $id != "" ) ) {
		$SQLQuery="select $idname from $tablename where $idname=$id";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");          
		$SQLNumRows = pg_numrows($SQLQueryResults);
		pg_freeresult($SQLQueryResults) or         
			die(pg_errormessage() . "<BR>\n");
	}
        return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  filterdropdown                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  An HTML control to list the available filters.     */
/*               The filter will display private or global filters  */
/*               and global filters will say (Global Filter) next   */
/*               to the description                                 */
/*                                                                  */
/********************************************************************/
function filterdropdown ($dbsocket, $fieldname, $userid, $tabs=0, $cr=0, $br=0, $lines=1, $selected="", $owneronly=0) {

	/* Gather the list of relevant filters */
	if ( ! $owneronly ) {
		$SQLQuery="select * from Syslog_TFilter where ( ( TFilter_UserOrGlobal = 1 ) and ( TLogin_ID = $userid ) ) or ( ( TFilter_UserOrGlobal = 2 ) ) order by TFilter_UserOrGlobal,TFilter_Desc";
	} else {
		$SQLQuery="select * from Syslog_TFilter where TLogin_ID = $userid order by TFilter_UserOrGlobal,TFilter_Desc";
	}
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	if ( $SQLNumRows > 0 ) {

		/* Assuming rows are returned, display the appropriate roles*/
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$desc=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilter_desc));
			$userorglobal=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilter_userorglobal));

			/* if userorglobal is 1, then the filter is a global filter and we should enumerate as such*/
			if ( $userorglobal == 1 ) { $userorglobal=""; } else { $userorglobal="(Global Filter)"; }
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilter_id));
			if ( $id==$selected ) {
				echo tabs($tabs+1) . "<option value=$id selected>$desc$userorglobal</option>\n";
			} else {
				echo tabs($tabs+1) . "<option value=$id>$desc$userorglobal</option>\n";
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
	die(pg_errormessage() . "<BR>\n");
}

/********************************************************************/
/*                                                                  */
/* Function:  getnextid                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Return the next value for a given PGSQL sequence   */
/*               number(PGSQL will then increment the sequence #    */
/*                                                                  */
/********************************************************************/
function getnextid ($dbsocket, $seqname) {

	$SQLQuery="select nextval('$seqname')";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	$Results="1";
	if ( $SQLNumRows > 0 ) {
		$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
			die(pg_errormessage()."<BR>\n");
		$Results=stripslashes(pgdatatrim($SQLQueryResultsObject->nextval));
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  premadetypedropdown                                   */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  An HTML control for providing type drop down boxes */
/*                                                                  */
/********************************************************************/
function premadetypedropdown ($dbsocket, $fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {

	$SQLQuery="select * from Syslog_TPremadeType order by TPremadeType_Desc";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$desc=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremadetype_desc));
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremadetype_id));
			if ( $id==$selected ) {
				echo tabs($tabs+1) . "<option value=$id selected>$desc</option>\n";
			} else {
				echo tabs($tabs+1) . "<option value=$id>$desc</option>\n";
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
	die(pg_errormessage() . "<BR>\n");
}

/********************************************************************/
/*                                                                  */
/* Function:  savesdropdown                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  An HTML control for providing a saved logs drop    */
/*               down boxes                                         */
/*                                                                  */
/********************************************************************/
function savesdropdown ($dbsocket, $fieldname, $userid, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {

	$SQLQuery="select * from Syslog_TSave where TLogin_ID=$userid order by TSave_Desc";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$desc=stripslashes(pgdatatrim($SQLQueryResultsObject->tsave_desc));
			$expiredate=stripslashes(pgdatatrim($SQLQueryResultsObject->tsave_expiredate));
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tsave_id));
			if ( $id==$selected ) {
				echo tabs($tabs+1) . "<option value=$id selected>$desc(expires $expiredate)</option>\n";
			} else {
				echo tabs($tabs+1) . "<option value=$id>$desc(expires $expiredate)</option>\n";
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
	die(pg_errormessage() . "<BR>\n");
}

/********************************************************************/
/*                                                                  */
/* Function:  userhavesaves                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Returns the number of saved syslogs a user has in  */
/*               the database                                       */
/*                                                                  */
/********************************************************************/
function userhavesaves($dbsocket,$userid) {

	$SQLQuery="select * from Syslog_TSave where TLogin_ID=$userid";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  userhavesaves                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Contains the saved syslog messages for a given     */
/*               TSave_ID                                           */
/*                                                                  */
/********************************************************************/
function savefilteredview($dbsocket,$saveid,$date,$time,$host,$facility,$severity,$message) {

	$host=fixappostrophe($host);
	$message=fixappostrophe($message);
	$SQLQuery="begin;insert into Syslog_TSaveData (TSaveData_Date,TSaveData_Time,TSaveData_Host,TSaveData_Facility,TSaveData_Severity,TSaveData_Message,TSave_ID) values ('$date','$time','$host',$facility,$severity,'$message',$saveid);commit;"; 
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);	
}

/********************************************************************/
/*                                                                  */
/* Function:  addfilter                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Save filter data to the database for a supplied    */
/*               filter_id                                          */
/*                                                                  */
/********************************************************************/
function addfilter($dbsocket,$filter,$filterid,$filterinclude,$filterorlevel,$startfacility,$stopfacility,$startseverity,$stopseverity) {

	$filterinclude=strval(stripslashes(pgdatatrim($filterinclude)));
	$SQLQuery="begin;insert into Syslog_TFilterData (TFilterData_Filter,TFilterData_Include,TFilter_ID,TFilterData_FilterOrLevel,TFilterData_StartFacility,TFilterData_StopFacility,TFilterData_StartSeverity,TFilterData_StopSeverity) values ('$filter',$filterinclude,$filterid,$filterorlevel,$startfacility,$stopfacility,$startseverity,$stopseverity);commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}                              

/********************************************************************/
/*                                                                  */
/* Function:  addfilterheader                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  add filter header associated by user               */
/*                                                                  */
/********************************************************************/
function addfilterheader($dbsocket,$userorglobal,$desc,$userid) {

	$desc=stripslashes(fixappostrophe($desc));
	$SQLQuery="begin;insert into Syslog_TFilter (TFilter_UserOrGlobal,TFilter_Desc,TLogin_ID) values ($userorglobal,'$desc',$userid);commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	$SQLQuery="select TFilter_ID from Syslog_TFilter where TLogin_ID=$userid and TFilter_Desc='$desc'";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $SQLNumRows ) {
		$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilter_id));
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  addsaveheader                                         */
/* Stability(1 low - 5 high):  3(need to retest)                    */
/* Description:  Add a header to link savedata records              */
/*                                                                  */
/********************************************************************/
function addsaveheader($dbsocket,$expdate,$descr,$time,$date,$userid) {

	$descr=ereg_replace("'","''",$descr);
	$save_id=getnextid ($dbsocket, "Syslog_TSave_TSave_ID_Seq");
	$SQLQuery="begin;insert into Syslog_TSave (TSave_ID,TSave_ExpireDate,TSave_Desc,TSave_Time,TSave_Date,TLogin_ID) values ($save_id,'$expdate','$descr','$time','$date',$userid);commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($save_id);
}

/********************************************************************/
/*                                                                  */
/* Function:  openmail                                              */
/* Stability(1 low - 5 high):  3(need to retest)                    */
/* Description:  The openmail function records when a process starts*/
/*                                                                  */
/********************************************************************/
function openmail($dbsocket,$date,$time,$userid) {

	$tmail_id=getnextid ($dbsocket, "syslog_tmail_tmail_id_seq");
	$SQLQuery="begin; insert into Syslog_TMail (TMail_ID,TMail_Open,TMail_Date,TMail_Time,TLogin_ID) values ($tmail_id,1,'$date','$time',$userid); commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($tmail_id);	
}

/********************************************************************/
/*                                                                  */
/* Function:  numlaunchrecords                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Given a mail_id, return how many records currently */
/*               exist                                              */
/*                                                                  */
/********************************************************************/
function numlaunchrecords($dbsocket,$mailid) {

	$SQLQuery="select * from Syslog_TLaunchQueue where TMail_ID=$mailid";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);	
}

/********************************************************************/
/*                                                                  */
/* Function:  numemailrecords                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Given a mail_id, return how many records currently */
/*               exist                                              */
/*                                                                  */
/********************************************************************/
function numemailrecords($dbsocket,$mailid) {

	$SQLQuery="select * from Syslog_TEmail where TMail_ID=$mailid";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);	
}

/********************************************************************/
/*                                                                  */
/* Function:  numdenials                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Return the number of denials that exit for either  */
/*               TRuleDeny or TPremadeDeny                          */
/*                                                                  */
/********************************************************************/
function numdenials($dbsocket,$denytype,$id) {

	$SQLNumRows=0;
	if ( $id != "" ) {
		if ( $denytype == 1 ) {
			$tablename="Syslog_TRuleDeny";
			$field="TRule_ID";
			$grab="TRuleDeny_ID";
		} else {
			$tablename="Syslog_TPremadeDeny";
			$field="TPremade_ID";
			$grab="TPremadeDeny_ID";
		}
		$SQLQuery="select $grab from $tablename where $field=$id";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($SQLNumRows);	
}

/********************************************************************/
/*                                                                  */
/* Function:  addmail                                               */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Save data for outgoing notification emails         */
/*                                                                  */
/********************************************************************/
function addmail($dbsocket,$email,$mailid,$tsyslogid,$desc="") {

	$desc=fixappostrophe($desc);
	$SQLQuery="begin;insert into Syslog_TEmail (TEmail_Email,TMail_ID,TSyslog_ID,TEmail_Desc) values ('$email',$mailid,$tsyslogid,'$desc');commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);	
}

/********************************************************************/
/*                                                                  */
/* Function:  cleanemail                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Blow away any stale emails, primarily used for     */
/*               taking care of 'stale' processors                  */
/*                                                                  */
/********************************************************************/
function cleanemail($dbsocket,$mailid) {

	$SQLQuery="begin;delete from Syslog_TEmail where TMail_ID=$mailid;commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);	
}

/********************************************************************/
/*                                                                  */
/* Function:  closeopenmail                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Delete TMail record w/ appropriate ID for either   */
/*               stale connections or properly closing out a        */
/*               processor                                          */
/*                                                                  */
/********************************************************************/
function closeopenmail($dbsocket,$mailid) {

	$SQLQuery="begin; delete from Syslog_TMail where TMail_ID=$mailid ; commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);	
}

/********************************************************************/
/*                                                                  */
/* Function:  ismailopen                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Determines if there is a mail record(ie processor) */
/*               that is in the DB.  If so, either a process is     */
/*               stale or processor is running behind               */
/*                                                                  */
/********************************************************************/
function ismailopen($dbsocket,$userid) {

	$Results=0;
	$SQLQuery="select * from Syslog_TMail where TMail_Open=1 and TLogin_ID=$userid";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $SQLNumRows > 0 ) {
		$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
			die(pg_errormessage()."<BR>\n");
		$Results=stripslashes(pgdatatrim($SQLQueryResultsObject->tmail_id));
	} 
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);	
}

/********************************************************************/
/*                                                                  */
/* Function:  dropdenials                                           */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Dual purpose function to delete tpremadetypedenial */
/*               and truledenial entries                            */
/*                                                                  */
/********************************************************************/
function dropdenial($dbsocket,$denytype,$denyid) {

        if ( $denytype == 1 ) {
                $tablename="Syslog_TRuleDeny";
                $field="TRuleDeny_ID";
        } else {
                $tablename="Syslog_TPremadeDeny";
                $field="TPremadeDeny_ID";
        }
	$SQLQuery="begin;delete from $tablename where $field=$denyid;commit;"; 
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  updatedenial                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Save changes to the appropriate denial rule        */
/*               whether it is TRuleDeny or TPremadeDeny            */
/*                                                                  */
/********************************************************************/
function updatedenial($dbsocket,$denytype,$id,$exp,$startfacility,$stopfacility,$startseverity,$stopseverity) {

        if ( $denytype == 1 ) {
                $tablename="Syslog_TRuleDeny";
                $field="TRuleDeny_ID";
		$fieldtoken="TRuleDeny_";
        } else {
                $tablename="Syslog_TPremadeDeny";
                $field="TPremadeDeny_ID";
		$fieldtoken="TPremadeDeny_";
        }
	$SQLQuery="begin;update $tablename set $fieldtoken"."Expression='$exp',$fieldtoken"."StartFacility=$startfacility,$fieldtoken"."StopFacility=$stopfacility,$fieldtoken"."StartSeverity=$startseverity,$fieldtoken"."StopSeverity=$stopseverity where $field=$id;commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  updateprocessid                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update the ID of the last processed syslog ID      */
/*                                                                  */
/********************************************************************/
function updateprocessid($dbsocket,$id,$hostid) {

	$SQLQuery="begin;update Syslog_TProcess set TProcess_ID=$id where THost_ID='$hostid';commit;";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	$Results=$SQLNumRows;
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  lastprocessedid                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  return the ID of the last processed syslog ID      */
/*                                                                  */
/********************************************************************/
function lastprocessedid($dbsocket,$hostid) {

	$SQLQuery="select TProcess_ID from Syslog_TProcess where THost_ID='$hostid'";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
		die(pg_errormessage()."<BR>\n");
	$Results=stripslashes(pgdatatrim($SQLQueryResultsObject->tprocess_id));
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  logincanseehosts                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  See if the supplied userid has the passed hostid   */
/*               associated with the account                        */
/*                                                                  */
/********************************************************************/
function logincanseehost($dbsocket,$userid,$hostid) {

	$SQLQuery="select * from Syslog_TCustomerProfile where TLogin_ID=$userid and THost_ID=$hostid";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	$Results=$SQLNumRows;
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($SQLNumRows);
}

/********************************************************************/
/*                                                                  */
/* Function:  pixruledropdown                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  This really is a html control that provides a      */
/*               prmade rule drop down box                          */
/*                                                                  */
/********************************************************************/
function pixruledropdown ($dbsocket, $fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $multiple="",$selected ="") {

	$SQLQuery="select TPremade_Code,TPremade_ID,TPremadeType_Desc from Syslog_TPremade,Syslog_TPremadeType where Syslog_TPremadeType.TPremadeType_ID=Syslog_TPremade.TPremadeType_ID order by TPremadeType_Desc,TPremade_Code";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines $multiple>\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_id));
			$code=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremade_code));
			$desc=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremadetype_desc));
			if ( $selected == $id ) {
				echo tabs($tabs+1) . "<option value=$id selected>($desc)$code</option>\n";
			} else {
				echo tabs($tabs+1) . "<option value=$id>($desc)$code</option>\n";
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
	die(pg_errormessage() . "<BR>\n");
}

/********************************************************************/
/*                                                                  */
/* Function:  hostdropdown                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provide host drop down box     */
/*               that only shows hosts that are allowed to be viewed*/
/*               by the user                                        */
/*                                                                  */
/********************************************************************/
function hostdropdown ($dbsocket, $sec_dbsocket, $fieldname, $userid=0, $group=0, $tabs=0, $cr=0, $br=0, $lines=1, $selected="", $unassigned=0) {

	if ( $unassigned == 0 ) {
		$SQLQuery="select DISTINCT THost_ID,THost_Host,TPremadeType_Desc from Syslog_THost,Syslog_TPremadeType where Syslog_THost.TPremadeType_ID=Syslog_TPremadeType.TPremadeType_ID order by TPremadeType_Desc,THost_Host";
	} else {
		$SQLQuery="select DISTINCT THost_ID,THost_Host,TPremadeType_Desc from Syslog_THost,Syslog_TPremadeType where Syslog_THost.TPremadeType_ID=Syslog_TPremadeType.TPremadeType_ID except select DISTINCT Syslog_THost.THost_ID,Syslog_THost.THost_Host,TPremadeType_Desc from Syslog_THost,Syslog_TPremadeType,Syslog_TProcessorProfile where Syslog_THost.TPremadeType_ID=Syslog_TPremadeType.TPremadeType_ID and Syslog_TProcessorProfile.THost_ID=Syslog_THost.THost_ID order by TPremadeType_Desc,THost_Host";
	}	
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines";
	if ( $lines > 1 ) {
		echo " multiple ";
	}
	echo ">\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$host=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_host));
			$hostid=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_id));
			$desc=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremadetype_desc));

			/* If the group leve is 2 aka Admin or the host is associated with the user... they can see the hosts */
			if ( ( $group >= 2 ) || ( (logincanseehost($dbsocket,$userid,$hostid)) && $group == 1 ) ) {
				if ( $host==$selected ) {
					echo tabs($tabs+1) . "<option value='$hostid' selected>($desc)$host</option>\n";
				} else {
					echo tabs($tabs+1) . "<option value='$hostid'>($desc)$host</option>\n";
				}
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
	die(pg_errormessage() . "<BR>\n");
}

/********************************************************************/
/*                                                                  */
/* Function:  hostdropdown1                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provide host drop down box     */
/*               that only shows hosts that are allowed to be viewed*/
/*               by the user                                        */
/*                                                                  */
/********************************************************************/
function hostdropdown1 ($dbsocket, $sec_dbsocket, $fieldname, $userid=0, $group=0, $tabs=0, $cr=0, $br=0, $lines=1, $selected="", $unassigned=0) {

	if ( $unassigned == 0 ) {
		$SQLQuery="select DISTINCT THost_ID,THost_Host,TPremadeType_Desc from Syslog_THost,Syslog_TPremadeType where Syslog_THost.TPremadeType_ID=Syslog_TPremadeType.TPremadeType_ID order by TPremadeType_Desc,THost_Host";
	} else {
		$SQLQuery="select DISTINCT THost_ID,THost_Host,TPremadeType_Desc from Syslog_THost,Syslog_TPremadeType where Syslog_THost.TPremadeType_ID=Syslog_TPremadeType.TPremadeType_ID except select DISTINCT Syslog_THost.THost_ID,Syslog_THost.THost_Host,TPremadeType_Desc from Syslog_THost,Syslog_TPremadeType,Syslog_TProcessorProfile where Syslog_THost.TPremadeType_ID=Syslog_TPremadeType.TPremadeType_ID and Syslog_TProcessorProfile.THost_ID=Syslog_THost.THost_ID order by TPremadeType_Desc,THost_Host";
	}	
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines";
	if ( $lines > 1 ) {
		echo " multiple ";
	}
	echo ">\n";
	echo "<option value='-1'>All Hosts</option>\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$host=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_host));
			$hostid=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_id));
			$desc=stripslashes(pgdatatrim($SQLQueryResultsObject->tpremadetype_desc));

			/* If the group leve is 2 aka Admin or the host is associated with the user... they can see the hosts */
			if ( ( $group >= 2 ) || ( (logincanseehost($dbsocket,$userid,$hostid)) && $group == 1 ) ) {
				if ( $host==$selected ) {
					echo tabs($tabs+1) . "<option value='$hostid' selected>($desc)$host</option>\n";
				} else {
					echo tabs($tabs+1) . "<option value='$hostid'>($desc)$host</option>\n";
				}
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
	die(pg_errormessage() . "<BR>\n");
}

/********************************************************************/
/*                                                                  */
/* Function:  monthdropdown                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a month drop down box */
/*                                                                  */
/********************************************************************/
function monthdropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {

	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	for ( $loop = 1 ; $loop != 13 ; $loop++ ) {	
		if ( $selected == date("M",mktime(0,0,0,$loop,1,2001)) ) {
			echo tabs($tabs+1) . "<option selected>" . date("M",mktime(0,0,0,$loop,1,2001)) . "</option>\n"; 
		} else {
			echo tabs($tabs+1) . "<option>" . date("M",mktime(0,0,0,$loop,1,2001)) . "</option>\n";
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}	

/********************************************************************/
/*                                                                  */
/* Function:  daydropdown                                           */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a day drop down box   */
/*                                                                  */
/********************************************************************/
function daydropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	for ( $loop = 1 ; $loop != 32 ; $loop++ ) {
		if ( $selected == $loop ) {
			echo tabs($tabs+1) . "<option selected>$loop</option>\n";
		} else {
			echo tabs($tabs+1) . "<option>$loop</option>\n";
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  yeardropdown                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a year drop down box  */
/*                                                                  */
/********************************************************************/
function yeardropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	for ( $loop = 2002 ; $loop != 2008 ; $loop++ ) {
		if ( $selected == $loop ) {
			echo tabs($tabs+1) . "<option selected>$loop</option>\n";
		} else {
			echo tabs($tabs+1) . "<option>$loop</option>\n";
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  hourdropdown                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a hour drop down box  */
/*                                                                  */
/********************************************************************/
function hourdropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	for ( $loop = 0 ; $loop != 24 ; $loop++ ) {
		if ( $selected == $loop ) {
			echo tabs($tabs+1) . "<option selected>$loop</option>\n";
		} else {
			echo tabs($tabs+1) . "<option>$loop</option>\n";
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  minutedropdown                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a minute drop down box*/
/*                                                                  */
/********************************************************************/
function minutedropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	$selected=strval($selected);
	for ( $loop = 0 ; $loop != 60 ; $loop++ ) {
		if ( $selected == $loop ) {
			echo tabs($tabs+1) . "<option selected>$loop</option>\n";
		} else {
			echo tabs($tabs+1) . "<option>$loop</option>\n";
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  logratesthreshold                                     */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides an expiration timer   */
/*               drop down box                                      */
/*                                                                  */
/********************************************************************/
function logratesthreshold($fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {

	if ( $selected < 100 ) { $selected = 100; }

	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	echo tabs($tabs+1) . "<option value=100"; 
	if ( $selected == "100" ) { echo " selected"; } 
	echo ">100</option>\n"; 
	echo tabs($tabs+1) . "<option value=200"; 
	if ( $selected == "200" ) { echo " selected"; } 
	echo ">200</option>\n"; 
	echo tabs($tabs+1) . "<option value=300"; 
	if ( $selected == "300" ) { echo " selected"; } 
	echo ">300</option>\n"; 
	echo tabs($tabs+1) . "<option value=400"; 
	if ( $selected == "400" ) { echo " selected"; } 
	echo ">400</option>\n"; 
	echo tabs($tabs+1) . "<option value=500"; 
	if ( $selected == "500" ) { echo " selected"; } 
	echo ">500</option>\n"; 
	echo tabs($tabs+1) . "<option value=600"; 
	if ( $selected == "600" ) { echo " selected"; } 
	echo ">600</option>\n"; 
	echo tabs($tabs+1) . "<option value=700"; 
	if ( $selected == "700" ) { echo " selected"; } 
	echo ">700</option>\n"; 
	echo tabs($tabs+1) . "<option value=800"; 
	if ( $selected == "800" ) { echo " selected"; } 
	echo ">800</option>\n"; 
	echo tabs($tabs+1) . "<option value=900"; 
	if ( $selected == "900" ) { echo " selected"; } 
	echo ">900</option>\n"; 
	echo tabs($tabs+1) . "<option value=1000";
	if ( $selected == "1000" ) { echo " selected"; } 
	echo ">1000</option>\n"; 
	echo tabs($tabs+1) . "<option value=2000";
	if ( $selected == "2000" ) { echo " selected"; } 
	echo ">2000</option>\n"; 
	echo tabs($tabs+1) . "<option value=3000";
	if ( $selected == "3000" ) { echo " selected"; } 
	echo ">3000</option>\n"; 
	echo tabs($tabs+1) . "<option value=4000";
	if ( $selected == "4000" ) { echo " selected"; } 
	echo ">4000</option>\n"; 
	echo tabs($tabs+1) . "<option value=5000";
	if ( $selected == "5000" ) { echo " selected"; } 
	echo ">5000</option>\n"; 
	echo tabs($tabs+1) . "<option value=6000";
	if ( $selected == "6000" ) { echo " selected"; } 
	echo ">6000</option>\n"; 
	echo tabs($tabs+1) . "<option value=7000";
	if ( $selected == "7000" ) { echo " selected"; } 
	echo ">7000</option>\n"; 
	echo tabs($tabs+1) . "<option value=8000";
	if ( $selected == "8000" ) { echo " selected"; } 
	echo ">8000</option>\n"; 
	echo tabs($tabs+1) . "<option value=9000";
	if ( $selected == "9000" ) { echo " selected"; } 
	echo ">9000</option>\n"; 
	echo tabs($tabs+1) . "<option value=10000";
	if ( $selected == "10000" ) { echo " selected"; } 
	echo ">10000</option>\n"; 


	echo tabs($tabs+1) . "<option value=11000";
	if ( $selected == "11000" ) { echo " selected"; } 
	echo ">11000</option>\n"; 

	echo tabs($tabs+1) . "<option value=12000";
	if ( $selected == "12000" ) { echo " selected"; } 
	echo ">12000</option>\n"; 

	echo tabs($tabs+1) . "<option value=13000";
	if ( $selected == "13000" ) { echo " selected"; } 
	echo ">13000</option>\n"; 
	echo tabs($tabs+1) . "<option value=14000";
	if ( $selected == "14000" ) { echo " selected"; } 
	echo ">14000</option>\n"; 
	echo tabs($tabs+1) . "<option value=15000";
	if ( $selected == "15000" ) { echo " selected"; } 
	echo ">15000</option>\n"; 
	echo tabs($tabs+1) . "<option value=16000";
	if ( $selected == "16000" ) { echo " selected"; } 
	echo ">16000</option>\n"; 
	echo tabs($tabs+1) . "<option value=17000";
	if ( $selected == "17000" ) { echo " selected"; } 
	echo ">17000</option>\n"; 
	echo tabs($tabs+1) . "<option value=18000";
	if ( $selected == "18000" ) { echo " selected"; } 
	echo ">18000</option>\n"; 
	echo tabs($tabs+1) . "<option value=19000";
	if ( $selected == "19000" ) { echo " selected"; } 
	echo ">19000</option>\n"; 

	echo tabs($tabs+1) . "<option value=20000";
	if ( $selected == "20000" ) { echo " selected"; } 
	echo ">20000</option>\n"; 
	echo tabs($tabs+1) . "<option value=30000";
	if ( $selected == "30000" ) { echo " selected"; } 
	echo ">30000</option>\n"; 
	echo tabs($tabs+1) . "<option value=40000";
	if ( $selected == "40000" ) { echo " selected"; } 
	echo ">40000</option>\n"; 
	echo tabs($tabs+1) . "<option value=50000";
	if ( $selected == "50000" ) { echo " selected"; } 
	echo ">50000</option>\n"; 
	echo tabs($tabs+1) . "<option value=100000";
	if ( $selected == "100000" ) { echo " selected"; } 
	echo ">100000</option>\n"; 
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  expireddropdown                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides an expiration timer   */
/*               drop down box                                      */
/*                                                                  */
/********************************************************************/
function expiredropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1, $selected="") {

	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	echo tabs($tabs+1) . "<option value=0"; 
	if ( $selected == "0" ) { echo " selected"; } 
	echo ">Never Expire</option>\n"; 
	echo tabs($tabs+1) . "<option value=86400";
	if ( $selected == 86400 ) { echo " selected"; }
	echo ">1 Day</option>\n"; 
	echo tabs($tabs+1) . "<option value=172800";
	if ( $selected == "172800" ) { echo " selected"; }
	echo ">2 Days</option>\n"; 
	echo tabs($tabs+1) . "<option value=604800";
	if ( $selected == "604800" ) { echo " selected"; }
	echo ">1 Week</option>\n"; 
	echo tabs($tabs+1) . "<option value=1209600";
 	if ( $selected == "1209600" ) { echo " selected"; }
	echo ">2 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=1814400";
	if ( $selected == "1814400" ) { echo " selected"; }
	echo ">3 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=2419200";
	if ( $selected == "2419200" ) { echo " selected"; }
	echo ">4 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=3024000";
	if ( $selected == "3024000" ) { echo " selected"; }
	echo ">5 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=3628800";
	if ( $selected == "3628800" ) { echo " selected"; }
	echo ">6 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=4233600";
	if ( $selected == "4233600" ) { echo " selected"; }
	echo ">7 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=4838400";
	if ( $selected == "4838400" ) { echo " selected"; }
	echo ">8 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=5443200";
	if ( $selected == "5443200" ) { echo " selected"; }
	echo ">9 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=6048000";
	if ( $selected == "6048000" ) { echo " selected"; }
	echo ">10 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=6652800";
	if ( $selected == "6652800" ) { echo " selected"; }
	echo ">11 Weeks</option>\n"; 
	echo tabs($tabs+1) . "<option value=7257600";
	if ( $selected == "7257600" ) { echo " selected"; }
	echo ">12 Weeks</option>\n"; 
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  pagesize                                              */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a page size drop      */
/*               down box                                           */
/*                                                                  */
/********************************************************************/
function pagesize($fieldname, $tabs=0, $cr=0, $br=0, $lines=1) {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	echo tabs($tabs+1) . "<option value=10>10 lines per page</option>\n";
	echo tabs($tabs+1) . "<option value=20>20 lines per page</option>\n";
	echo tabs($tabs+1) . "<option value=50>50 lines per page</option>\n";
	echo tabs($tabs+1) . "<option value=100 selected>100 lines per page</option>\n";
	echo tabs($tabs+1) . "<option value=200>200 lines per page</option>\n";
	echo tabs($tabs+1) . "<option value=500>500 lines per page</option>\n";
	echo tabs($tabs+1) . "<option value=1000>1000 lines per page</option>\n";
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  pagesize                                              */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A html control that provides a drop down box with  */
/*               time durations                                     */
/*                                                                  */
/********************************************************************/
function durationdropdown($fieldname, $tabs=0, $cr=0, $br=0, $lines=1) {
	
	echo tabs($tabs) . "<select name=$fieldname size=$lines>\n";
	echo tabs($tabs+1) . "<option value=60>+1 minute</option>\n";
	echo tabs($tabs+1) . "<option value=300>+5 minutes</option>\n";
	echo tabs($tabs+1) . "<option value=600>+10 minutes</option>\n";
	echo tabs($tabs+1) . "<option value=900>+15 minutes</option>\n";
	echo tabs($tabs+1) . "<option value=1200>+20 minutes</option>\n";
	echo tabs($tabs+1) . "<option value=1500>+25 minutes</option>\n";
	echo tabs($tabs+1) . "<option value=1800>+30 minutes</option>\n";
	echo tabs($tabs+1) . "<option value=3600>+1 hour</option>\n";
	echo tabs($tabs+1) . "<option value=7200>+2 hours</option>\n";
	echo tabs($tabs+1) . "<option value=14400>+4 hours</option>\n";
	echo tabs($tabs+1) . "<option value=28800>+8 hours</option>\n";
	echo tabs($tabs+1) . "<option value=43200>+12 hours</option>\n";
	echo tabs($tabs+1) . "<option value=57600>+16 hours</option>\n";
	echo tabs($tabs+1) . "<option value=86400 selected>+1 day</option>\n";
	echo tabs($tabs+1) . "<option value=172800>+2 days</option>\n";
	echo tabs($tabs+1) . "<option value=259200>+3 days</option>\n";
	echo tabs($tabs+1) . "<option value=345600>+4 days</option>\n";
	echo tabs($tabs+1) . "<option value=432000>+5 days</option>\n";
	echo tabs($tabs+1) . "<option value=518400>+6 days</option>\n";
	echo tabs($tabs+1) . "<option value=604800>+7 days</option>\n";
	echo tabs($tabs+1) . "<option value=120960>+2 Weeks</option>\n";
	echo tabs($tabs+1) . "<option value=181440>+3 Weeks</option>\n";
	echo tabs($tabs+1) . "<option value=241920>+4 Weeks</option>\n";
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
}

/********************************************************************/
/*                                                                  */
/* Function:  addalert                                              */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  creates alert entry in the Syslog_TAlert table     */
/*                                                                  */
/********************************************************************/
function addalert($dbsocket,$date,$time,$info,$syslogid) {

	$Results=0;
	$info = setupappostrophe($info);
	$SQLQuery="begin;insert into Syslog_TAlert (TAlert_Date,TAlert_Time,TAlert_Info,TSyslog_ID) values ('$date','$time','$info',$syslogid);commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results = 1 ; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  gethostid                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Return the THost_ID for a given 'host' name        */
/*                                                                  */
/********************************************************************/
function gethostid($dbsocket,$host) {

        $Results=0;
        $SQLQuery="select THost_ID from Syslog_THost where THost_Host='$host'";
        $SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
                die(pg_errormessage()."<BR>\n");
        if ( $SQLQueryResults ) {
                $SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
                        die(pg_errormessage()."<BR>\n");
                $Results=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_id));
        }
        pg_freeresult($SQLQueryResults) or
                die(pg_errormessage() . "<BR>\n");
        return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  gethost                                               */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Return the THost_Host for a given THost_ID         */
/*                                                                  */
/********************************************************************/
function gethost($dbsocket,$hostid) {

	$Results=0;
	$SQLQuery="select THost_Host from Syslog_THost where THost_ID=$hostid";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) {
		$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
			die(pg_errormessage()."<BR>\n");
		$Results=stripslashes(pgdatatrim($SQLQueryResultsObject->thost_host));
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  updatehostprocess                                     */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update the TProcess_ID for a given THost_ID        */
/*                                                                  */
/********************************************************************/
function updatehostprocess($dbsocket,$hostid,$processid) {

	$Results=0;
	$SQLQuery="begin;update Syslog_TProcess set TProcess_ID=$processid where THost_ID='$hostid';commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
} 

/********************************************************************/
/*                                                                  */
/* Function:  drophostprocess                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update the TProcess_ID for a given THost_ID        */
/*                                                                  */
/********************************************************************/
function drophostprocess($dbsocket,$hostid) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TProcess where THost_ID='$hostid';commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
} 

/********************************************************************/
/*                                                                  */
/* Function:  addhostprocess                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Add a record to the Syslog_TProcess table for a    */
/*               given THost_ID                                     */
/*                                                                  */
/********************************************************************/
function addhostprocess($dbsocket,$hostid) {

	$Results=0;
	$SQLQuery="begin;insert into Syslog_TProcess (TProcess_ID,THost_ID) values (0,'$hostid');commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
} 

/********************************************************************/
/*                                                                  */
/* Function:  addhost                                               */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Add a record to the Syslog_TProcess table for a    */
/*               given THost_ID                                     */
/*                                                                  */
/********************************************************************/
function
addhost($dbsocket,$host,$syslogexpire,$alertexpire,$typeid,$hostrate,$dologrep,$revreq) {

	$Results=0;
	$host=fixappostrophe(stripslashes(pgdatatrim($host)));
	$syslogexpire=fixappostrophe(stripslashes(pgdatatrim($syslogexpire)));
	$alertexpire=fixappostrophe(stripslashes(pgdatatrim($alertexpire)));
	$typeid=fixappostrophe(stripslashes(pgdatatrim($typeid)));
	if ($dologrep != 1) {
		$dologrep = 0;
		$revreq = 0;
	}
	if ( $hostrate < 100 ) { $hostrate = 100; }
	$SQLQuery="begin;insert into Syslog_THost (THost_Host,THost_AlertExpire,THost_LogExpire,TPremadeType_ID,THost_Rate, do_logreport, log_reviewers) values ('$host',$alertexpire,$syslogexpire,$typeid,$hostrate,$dologrep,$revreq);commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);
}		

/********************************************************************/
/*                                                                  */
/* Function:  updatehost                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update a record to the Syslog_TProcess table for a */
/*               given THost_ID                                     */
/*                                                                  */
/********************************************************************/
function
updatehost($dbsocket,$hostid,$host,$syslogexpire=0,$alertexpire=0,$typeid,$hostrate,$dologrep,$revreq) {

	$Results=0;
	$host=fixappostrophe(stripslashes(pgdatatrim($host)));
	$syslogexpire=fixappostrophe(stripslashes(pgdatatrim($syslogexpire)));
	$alertexpire=fixappostrophe(stripslashes(pgdatatrim($alertexpire)));
	$typeid=fixappostrophe(stripslashes(pgdatatrim($typeid)));
	if ( $dologrep != 1) {
		$dologrep = 0;
		$revreq = 0;
	}
	if ( $hostrate < 100 ) { $hostrate = 100; }
	$SQLQuery="begin;update Syslog_THost set THost_Host='$host',THost_AlertExpire=$alertexpire,THost_LogExpire=$syslogexpire,TPremadeType_ID=$typeid,THost_Rate=$hostrate,do_logreport=$dologrep,log_reviewers=$revreq where THost_ID=$hostid;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  drophostid                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Drop a record from the Syslog_THost table for a    */
/*               given THost_ID                                     */
/*                                                                  */
/********************************************************************/
function drophostid($dbsocket,$hostid) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_THost where THost_ID=$hostid;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}
	
/********************************************************************/
/*                                                                  */
/* Function:  droppremade                                           */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Drop a record from the Syslog_TPremade table for a */
/*               given TPremade_ID                                  */
/*                                                                  */
/********************************************************************/
function droppremade($dbsocket,$id) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TPremade where TPremade_ID=$id;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  updateequiptype                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update an equipment type entry in the Syslog_..    */
/*               TPremadeType table                                 */
/*                                                                  */
/********************************************************************/
function updateequiptype($dbsocket,$typeid,$typedesc, $logwatch) {

	$Results=0;
	$typedesc=fixappostrophe(stripslashes(pgdatatrim($typedesc)));
	$typeid=fixappostrophe(stripslashes(pgdatatrim($typeid)));
	$logwatch=fixappostrophe(stripslashes(pgdatatrim($logwatch)));
	if ( $typedesc != "" ) {
		$SQLQuery = "begin;update syslog_tpremadetype set logwatch_cmd='$logwatch', tpremadetype_desc='$typedesc' where tpremadetype_id=$typeid;commit;"; 
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  dropequiptype                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Drop an equipment type entry in the Syslog_..      */
/*               TPremadeType table for a given TPremadetype_ID     */
/*                                                                  */
/********************************************************************/
function dropequiptype($dbsocket,$typeid) {

	$Results=0;
	$typedesc=fixappostrophe(stripslashes(pgdatatrim($typeid)));
	if ( $typeid != "" ) {
		$SQLQuery = "begin;delete from syslog_tpremadetype where tpremadetype_id=$typeid;commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  addequiptype                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Add an equipment type entry in the Syslog_....     */
/*               TPremadeType table                                 */
/*                                                                  */
/********************************************************************/
function addequiptype($dbsocket,$typedesc, $logwatch) {

	$Results=0;
	$typedesc=fixappostrophe(stripslashes(pgdatatrim($typedesc)));
	$logwatch=fixappostrophe(stripslashes(pgdatatrim($logwatch)));
	if ( $typedesc != "" ) {
		$SQLQuery = "begin;insert into syslog_tpremadetype (tpremadetype_desc, logwatch_cmd) values ('$typedesc', '$logwatch');commit;"; 
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  addpremaderule                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Add a premade rule entry in the Syslog_TPremadeRule*/
/*               table                                              */
/*                                                                  */
/********************************************************************/
function addpremaderule($dbsocket,$code,$desc,$typeid,$startfacility,$stopfacility,$startseverity,$stopseverity,$levelorrule,$launchid,$threshold,$thresholdtype) {

	$code=pgdatatrim($code);
	$desc=pgdatatrim($desc);
	$typeid=fixappostrophe(stripslashes(pgdatatrim($typeid)));
	$launchid=fixappostrophe(stripslashes(pgdatatrim($launchid)));
	if ( strval($threshold) < 0 ) { $threshold=0; }
	if ( ( strval($thresholdtype) < 0 ) || ( strval($thresholdtype) > 2 ) ) { $thresholdtype=0; }
	if ( $startfacility > $stopfacility ) {
		$temp=$stopfacility;
		$stopfacility=$startfacility;
		$startfacility=$temp;
	} 
	if ( $startseverity > $stopseverity ) {
		$temp=$stopseverity;
		$stopseverity=$startseverity;
		$startseverity=$temp;
	} 
	if ( ( $levelorrule == "" ) || ( $levelorrule < 0 ) || ( $levelorrule > 3 ) ){ $levelorrule = 1; }
	$Results=0;
	$SQLQuery="begin;insert into Syslog_TPremade (TPremade_Code,TPremade_Desc,TPremadeType_ID,TPremade_StartFacility,TPremade_StopFacility,TPremade_StartSeverity,TPremade_StopSeverity,TPremade_PremadeOrLevel,TLaunch_ID,TPremade_Threshold,TPremade_ThresholdType) values ('$code','$desc',$typeid,$startfacility,$stopfacility,$startseverity,$stopseverity,$levelorrule,$launchid,$threshold,$thresholdtype);commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  updatepremaderule                                     */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update a premade rule in the Syslog_TPremade table */
/*                                                                  */
/********************************************************************/
function updatepremaderule($dbsocket,$id,$code,$desc,$typeid,$startfacility,$stopfacility,$startseverity,$stopseverity,$levelorrule,$launchid,$threshold,$thresholdtype) {

	$id=stripslashes(pgdatatrim($id));
	$code=pgdatatrim($code);
	$desc=pgdatatrim($desc);
	$typeid=fixappostrophe(stripslashes(pgdatatrim($typeid)));
	if ( strval($threshold) < 0 ) { $threshold=0; }
	if ( ( strval($thresholdtype) < 0 ) || ( strval($thresholdtype) > 2 ) ) { $thresholdtype=0; }
	if ( $startfacility > $stopfacility ) {
		$temp=$stopfacility;
		$stopfacility=$startfacility;
		$startfacility=$temp;
	} 
	if ( $startseverity > $stopseverity ) {
		$temp=$stopseverity;
		$stopseverity=$startseverity;
		$startseverity=$temp;
	} 
	if ( ( $levelorrule == "" ) || ( $levelorrule < 0 ) || ( $levelorrule > 3 ) ){ $levelorrule = 1; }
	$Results=0;
	$SQLQuery="begin;update Syslog_TPremade set TPremade_Code='$code',TPremade_Desc='$desc',TPremadeType_ID=$typeid,TPremade_StartFacility=$startfacility,TPremade_StopFacility=$stopfacility,TPremade_StartSeverity=$startseverity,TPremade_StopSeverity=$stopseverity,TPremade_PremadeOrLevel=$levelorrule,TLaunch_ID=$launchid,TPremade_Threshold=$threshold,TPremade_ThresholdType=$thresholdtype where TPremade_ID=$id;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  clonedenials                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Redundant code to add a rule to Syslog_TRuleDeny   */
/*               table while supplying the TRuleDeny_ID.  This      */ 
/*               should not be necessary.  8(                       */
/*                                                                  */
/********************************************************************/
function clonedenials($dbsocket,$id,$newid) {

	$SQLQuery="select * from Syslog_TRuleDeny where TRule_ID=$id order by TRuleDeny_ID";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $SQLNumRows ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$denyexp=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_expression));
			$denystartfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_startfacility));
			$denystopfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_stopfacility));
			$denystartseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_startseverity));
			$denystopseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->truledeny_stopseverity));
			$SQLQuery="begin;insert into Syslog_TRuleDeny (TRule_ID,TRuleDeny_Expression,TRuleDeny_StartFacility,TRuleDeny_StopFacility,TRuleDeny_StartSeverity,TRuleDeny_StopSeverity) values ($newid,'$denyexp',$denystartfacility,$denystopfacility,$denystartseverity,$denystopseverity);commit;";
        		$DenySQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
        		        die(pg_errormessage()."<BR>\n");
			pg_freeresult($DenySQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
}

/********************************************************************/
/*                                                                  */
/* Function:  clonehostrule                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Redundant code to add a rule to Syslog_TRule table */
/*               while supplying the TRule_ID.  This should not be  */
/*               necessary.  8(                                     */
/*                                                                  */
/********************************************************************/
function clonehostrule($dbsocket,$id,$hostid,$alert,$email,$expression,$desc,$startfacility,$stopfacility,$startseverity,$stopseverity,$levelorrule,$launchid,$threshold,$thresholdtype,$starttime,$endtime,$timertype,$daysofweek) {

	$hostid=fixappostrophe(stripslashes(pgdatatrim($hostid)));
	$alert=fixappostrophe(stripslashes(pgdatatrim($alert)));
	$email=fixappostrophe(stripslashes(pgdatatrim($email)));
	$desc=fixappostrophe(stripslashes(pgdatatrim($desc)));
	$expression=fixappostrophe(stripslashes(pgdatatrim($expression)));

	if ( strval($threshold) < 0 ) { $threshold=0; }
	if ( strval($timertype) > 3 ) { $timertype=3; }
	if ( strval($timertype) < 0 ) { $timertype=0; }
	if ( ( strval($daysofweek) >= 128 ) || ( strval($daysofweek) < 0 ) ) { $daysofweek=0; }
	if ( ( strval($thresholdtype) < 0 ) || ( strval($thresholdtype) > 2 ) ) { $thresholdtype=0; }
	if ( ( strval($timertype) < 0 ) || ( strval($timertype) > 3 ) ) { $timertype=0; }
	if ( $launchid == "" ) { $launchid = 0; }
	if ( $startfacility > $stopfacility ) {
		$temp=$stopfacility;
		$stopfacility=$startfacility;
		$startfacility=$temp;
	} 
	if ( $startseverity > $stopseverity ) {
		$temp=$stopseverity;
		$stopseverity=$startseverity;
		$startseverity=$temp;
	} 
	if ( strval($thresholdtype) < 1 ) { $thresholdtype = 0; }
	if ( strval($starttime) < 1 ) { $starttime = 0; }
	if ( strval($endtime) < 1 ) { $endtime = 0; }
	if ( strval($timertype) < 1 ) { $timertype = 0; }
	if ( strval($daysofweek) < 1 ) { $daysofweek = 0; }
	if ( ( $levelorrule == "" ) || ( $levelorrule < 0 ) || ( $levelorrule > 3 ) ){ $levelorrule = 1; }
	$Results=0;
	$SQLQuery="begin;insert into Syslog_TRule (TRule_ID, TRule_LogAlert, TRule_Email, TRule_Expression, TRule_Desc, THost_ID, TRule_StartFacility, TRule_StopFacility, TRule_StartSeverity, TRule_StopSeverity, TRule_RuleOrLevel, TLaunch_ID, TRule_Threshold, TRule_ThresholdType, TRule_StartTime, TRule_EndTime, TRule_TimerType, TRule_DaysofWeek) values ($id, $alert, '$email', '$expression', '$desc', $hostid, $startfacility, $stopfacility, $startseverity, $stopseverity, $levelorrule, $launchid, $threshold, $thresholdtype, $starttime, $endtime, $timertype, $daysofweek);commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  dropruleid                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Drop a given rule_id from the Syslog_TRule table   */
/*                                                                  */
/********************************************************************/
function dropruleid($dbsocket,$id) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TRule where TRule_ID=$id;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  addhostrule                                           */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Add a rule to the Syslog_TRule table for a given   */
/*               host                                               */
/*                                                                  */
/********************************************************************/
function addhostrule($dbsocket,$hostid,$alert,$email,$expression,$desc,$startfacility,$stopfacility,$startseverity,$stopseverity,$levelorrule,$launchid,$threshold,$thresholdtype,$starttime,$endtime,$timertype,$daysofweek) {

	$hostid=fixappostrophe(stripslashes(pgdatatrim($hostid)));
	$alert=fixappostrophe(stripslashes(pgdatatrim($alert)));
	$email=fixappostrophe(stripslashes(pgdatatrim($email)));
	$desc=fixappostrophe(stripslashes(pgdatatrim($desc)));
	$expression=pgdatatrim($expression);
	if ( ( strval($thresholdtype) < 0 ) || ( strval($thresholdtype) > 2 ) ) { $thresholdtype=0; }
	if ( ( strval($daysofweek) >= 128 ) || ( strval($daysofweek) < 0 ) ) { $daysofweek=0; }
	if ( ( strval($timertype) < 0 ) || ( strval($timertype) > 3 ) ) { $timertype=0; }
	if ( strval($threshold) < 0 ) { $threshold=0; }
	if ( $startfacility > $stopfacility ) {
		$temp=$stopfacility;
		$stopfacility=$startfacility;
		$startfacility=$temp;
	} 
	if ( $startseverity > $stopseverity ) {
		$temp=$stopseverity;
		$stopseverity=$startseverity;
		$startseverity=$temp;
	} 
	if ( ( $levelorrule == "" ) || ( $levelorrule < 0 ) || ( $levelorrule > 3 ) ){ $levelorrule = 1; }
	$Results=0;
	/* ,$starttime,$endtime,$timertype,$daysofweek */
	$SQLQuery="begin;insert into Syslog_TRule (TRule_LogAlert, TRule_Email, TRule_Expression, TRule_Desc, THost_ID, TRule_StartFacility, TRule_StopFacility, TRule_StartSeverity, TRule_StopSeverity, TRule_RuleOrLevel, TLaunch_ID, TRule_Threshold, TRule_ThresholdType, TRule_StartTime, TRule_EndTime, TRule_TimerType, TRule_DaysofWeek) values ($alert, '$email', '$expression', '$desc', $hostid, $startfacility, $stopfacility, $startseverity, $stopseverity, $levelorrule, $launchid, $threshold, $thresholdtype, $starttime, $endtime, $timertype, $daysofweek);commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  updatehostrule                                        */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Update a given host rule.                          */
/*                                                                  */
/********************************************************************/
function updatehostrule($dbsocket,$id,$hostid,$alert,$email,$expression,$desc,$startfacility,$stopfacility,$startseverity,$stopseverity,$levelorrule,$launchid,$threshold,$thresholdtype,$starttime,$endtime,$timertype,$daysofweek) {

	$id=fixappostrophe(stripslashes(pgdatatrim($id)));
	$launchid=fixappostrophe(stripslashes(pgdatatrim($launchid)));
	$hostid=fixappostrophe(stripslashes(pgdatatrim($hostid)));
	$alert=fixappostrophe(stripslashes(pgdatatrim($alert)));
	$email=fixappostrophe(stripslashes(pgdatatrim($email)));
	$expression=pgdatatrim($expression);
	$desc=fixappostrophe(stripslashes(pgdatatrim($desc)));
	if ( strval($threshold) < 0 ) { $threshold=0; }
	if ( ( strval($thresholdtype) < 0 ) || ( strval($thresholdtype) > 2 ) ) { $thresholdtype=0; }
	if ( ( strval($daysofweek) >= 128 ) || ( strval($daysofweek) < 0 ) ) { $daysofweek=0; }
	if ( ( strval($timertype) < 0 ) || ( strval($timertype) > 3 ) ) { $timertype=0; }
	if ( $startfacility > $stopfacility ) {
		$temp=$stopfacility;
		$stopfacility=$startfacility;
		$startfacility=$temp;
	} 
	if ( $startseverity > $stopseverity ) {
		$temp=$stopseverity;
		$stopseverity=$startseverity;
		$startseverity=$temp;
	} 
	if ( ( $levelorrule == "" ) || ( $levelorrule < 0 ) || ( $levelorrule > 3 ) ){ $levelorrule = 1; }
	$Results=0;
	$SQLQuery="begin;update Syslog_TRule set TRule_LogAlert=$alert, TRule_Email='$email', TRule_Expression='$expression', THost_ID=$hostid, TRule_Desc='$desc', TRule_StartFacility=$startfacility, TRule_StopFacility=$stopfacility, TRule_StartSeverity=$startseverity, TRule_StopSeverity=$stopseverity, TRule_RuleOrLevel=$levelorrule, TLaunch_ID=$launchid, TRule_Threshold=$threshold, TRule_ThresholdType=$thresholdtype, TRule_StartTime=$starttime, TRule_EndTime=$endtime, TRule_TimerType=$timertype, TRule_DaysofWeek=$daysofweek where TRule_ID=$id;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  dropcustomerhost                                      */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Disassociate a host with a given customer          */
/*                                                                  */
/********************************************************************/
function dropcustomerhost($dbsocket,$id) {

	$Results=0;
	$SQLQuery="begin;delete from Syslog_TCustomerProfile where TCustomerProfile_ID=$id;commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}
	
/********************************************************************/
/*                                                                  */
/* Function:  addcustomerhost                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Associate a host with a given customer             */
/*                                                                  */
/********************************************************************/
function addcustomerhost($dbsocket,$hostid,$userid,$allowedit) {

	$hostid=fixappostrophe(stripslashes(pgdatatrim($hostid)));
	$userid=fixappostrophe(stripslashes(pgdatatrim($userid)));
	if ( $allowedit != 1 ) { $allowedit = 0; }
	$Results=0;
	$SQLQuery="begin;insert into Syslog_TCustomerProfile (THost_ID,TLogin_ID,TCustomerProfile_EditRules) values ($hostid,$userid,$allowedit);commit;";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { $Results=1; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  numberofmonth                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Given the short name for a month, ie. 'jan',       */
/*               return the decimal number for that month, ie. 1    */
/*                                                                  */
/********************************************************************/
function numberofmonth($month) {

	$Results=0;
        for ( $loop = 1 ; $loop != 13 ; $loop++ ) {
                if ( $month == date("M",mktime(0,0,0,$loop,1,2001) ) ) { $Results = $loop; }  
        }
	if ( strlen($Results) != 2 ) { $Results = "0" . $Results; }
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  numberofrecords                                       */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A table inspecific function allowing generic       */
/*               queries.  The function will return the number of   */
/*               records returned by the query.                     */
/*                                                                  */
/********************************************************************/
function numberofrecords($dbsocket,$fieldname,$tablename) {
	
	$Results=0;
	$SQLQuery="select count($fieldname) from $tablename";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { 
		$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
			die(pg_errormessage()."<BR>\n");
		$Results=$SQLQueryResultsObject->count;
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  numberofhostsusingtype                                */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Report the number of hosts using a given equipment */
/*               type of TPremadeType_ID                            */
/*                                                                  */
/********************************************************************/
function numberofhostsusingtype($dbsocket,$typeid) {
	
	$Results=0;
	$SQLQuery="select count(tpremadetype_id) from Syslog_THost where TPremadeType_ID=$typeid";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	if ( $SQLQueryResults ) { 
		$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
			die(pg_errormessage()."<BR>\n");
		$Results=$SQLQueryResultsObject->count;
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	return($Results);                         
}

/********************************************************************/
/*                                                                  */
/* Function:  addsuspend                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Add an entry to the syslog_tsuspend process table  */
/*                                                                  */
/********************************************************************/
function addsuspend($dbsocket,$id) {

	$Results=0;
	$id=fixappostrophe(stripslashes(pgdatatrim($id)));
	if ( $id > 0 ) {
		$SQLQuery = "begin;insert into syslog_tsuspend (TLogin_ID,TSuspend_Status) values ($id,1);commit;"; 
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  deletesuspend                                         */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Delete an entry to the syslog_tsuspend table       */
/*                                                                  */
/********************************************************************/
function deletesuspend($dbsocket,$id) {

	$Results=0;
	$id=fixappostrophe(stripslashes(pgdatatrim($id)));
	if ( $id > 0 ) {
		$SQLQuery = "begin;delete from syslog_tsuspend where tlogin_id=$id;commit;";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results=1; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

%>
