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

/********************************************/
/*                                          */
/* Purpose:  To provide the framework and   */
/* functions to facilitate scalable, secure */
/* and easy to manage systems.  SecFrame db */ 
/* should never be accessed by anything but */
/* secframe functions.                      */
/*                                          */
/********************************************/

require_once('pgsql.php');

define("SECDB", "securityframework");
define("SECPASS", "voQ3jV1x");
define("SECFRAMEVER","1.0");

/***********Functions***********
function sec_verifypassword ($password ) {
function sec_startbody($tabs=0) {
function sec_updateappperm ($dbsocket, $id, $usergroup, $ugid, $allowaccess, $app_id, $priority) {
function groupmemberdropdownbox ($dbsocket, $fieldname, $groupid, $member=1,$tabs=0, $cr=1, $br=0, $lines=1, $multi=0, $selected="") {
function sec_updateapp ($dbsocket, $id, $appname, $appdesc) {
function sec_updategroupmembers ($dbsocket,$groupmembersid,$userid,$groupid) {
function sec_updategroup ($dbsocket, $groupid, $groupname, $groupdesc) {
function userdropdownbox ($dbsocket, $fieldname, $tabs=0, $cr=1, $br=0, $lines=1, $selected="", $groupid="") {
function appdropdownbox ($dbsocket, $fieldname, $tabs=0, $cr=1, $br=0, $lines=1, $selected="") {
function groupdropdownbox ($dbsocket, $fieldname, $tabs=0, $cr=1, $br=0, $lines=1, $selected="") {
function sec_accessallowed($dbsocket,$userid,$appid) {
function sec_dbconnect() {
function sec_groupmember($dbsocket,$userid,$groupid) {
function sec_groupname($dbsocket,$groupid) {
function sec_appname($dbsocket,$appid) {
function sec_appnametoid($dbsocket,$appname) {
function sec_usernametoid($dbsocket,$username) {
function sec_groupnametoid($dbsocket,$groupname) {
function sec_username($dbsocket,$userid) {
function sec_delid($dbsocket,$tablename,$idname,$id) {
function sec_idexist($dbsocket,$tablename,$idname,$id) {
function sec_addgroup($dbsocket,$groupname,$groupdesc) {
function sec_addappperm($dbsocket,$usergroup,$ugid,$allowaccess,$appid,$priority) {
function sec_getpriority($dbsocket,$appid) {
function sec_addgroupmembers($dbsocket,$userid,$groupid) {
function sec_dropgroupmembers($dbsocket,$userid,$groupid) {
function sec_addapp($dbsocket,$appname,$appdesc) {
function sec_addlogin($dbsocket,$username,$password,$name,$email,$home,$work,$cell,$pager,
		$address1,$address2,$city,$state,$zip) {
sec_updatelogin ($dbsocket,$id,$username,$password,$name,$email,$home,$work,$cell,$pager,
		$address1,$address2,$city,$state,$zip) 
***********Functions***********/

/********************************************/
/*                                          */
/* Funciton:  sec_verifypassword            */
/*                                          */
/* Verify that password meets content       */
/* criteria of having something other than  */
/* all lowercase or all uppercase letters   */
/*                                          */
/********************************************/

function sec_verifypassword ($password ) {

	$flag=0;
	$Results=strtr($password,"abcdefghijklmnopqrstuvwxyz","aaaaaaaaaaaaaaaaaaaaaaaaaa");
	$flag1=0;
	for ( $loop=0; $loop != strlen($Results) ; $loop++ ) {
		if ( substr($Results, $loop, 1) != 'a' ) { $flag1=1; };
	}
	$Results=strtr($password,"ABCDEFGHIJKLMNOPQRSTUVWXYZ","AAAAAAAAAAAAAAAAAAAAAAAAAA");
	$flag2=0;
	for ( $loop=0; $loop != strlen($Results) ; $loop++ ) {
		if ( substr($Results, $loop, 1) != 'A' ) { $flag2=1; };
	}
	if ( $flag1 && $flag2 ) {
		$flag=1;
	}
	return($flag);
}

/********************************************/
/*                                          */
/* Function:  sec_updatelogin               */
/*                                          */
/* Purpose:  Update TLogin given valid info */
/*                                          */
/********************************************/

function sec_updatelogin ($dbsocket,$id,$username,$password,$name,$email,$home,$work,$cell,$pager,$address1,$address2,$city,$state,$zip ) {

	$Results=0;
	if ( ( !embededsql($id) ) &&
		( !embededsql($username) ) &&
		( !embededsql($password) ) &&
		( !embededsql($name) ) &&
		( !embededsql($email) ) &&
		( !embededsql($home) ) &&
		( !embededsql($work) ) &&
		( !embededsql($cell) ) &&
		( !embededsql($pager) ) &&
		( !embededsql($address1) ) &&
		( !embededsql($address2) ) &&
		( !embededsql($city) ) &&
		( !embededsql($state) ) &&
		( !embededsql($zip) ) ) {
		$id=stripslashes(pgdatatrim($id));
		$username=stripslashes(substr(pgdatatrim($username),0,128));
		$password=stripslashes(substr(pgdatatrim($password),0,36));
		$name=stripslashes(substr(pgdatatrim($name),0,40));        
		$email=stripslashes(substr(pgdatatrim($email),0,40));
		$home=stripslashes(substr(pgdatatrim($home),0,20));  
		$cell=stripslashes(substr(pgdatatrim($cell),0,20));
		$work=stripslashes(substr(pgdatatrim($work),0,20));
		$pager=stripslashes(substr(pgdatatrim($pager),0,20));
		$address1=stripslashes(substr(pgdatatrim($address1),0,40));
		$address2=stripslashes(substr(pgdatatrim($address2),0,40));
		$city=stripslashes(substr(pgdatatrim($city),0,40));        
		$state=stripslashes(substr(pgdatatrim($state),0,40));
		if ( ( $username != "" ) && ( is_string($username) ) &&
			( $password != "" ) && ( is_string($password) ) &&
			( $name != "" ) && ( is_string($name) ) &&
			( $password != "" ) && ( is_string($password) ) &&
			( sec_idexist($dbsocket,"SecFrame_TLogin","TLogin_ID",$id) ) &&
			( is_string($email) ) &&
			( is_string($home) ) &&
			( is_string($work) ) &&
			( is_string($cell) ) &&
			( is_string($pager) ) &&
			( is_string($address1) ) &&
			( is_string($address2) ) &&
			( is_string($city) ) &&
			( is_string($state) ) &&
			( is_string($zip) ) ) { 
			if ( strlen($password) < 32 ) { 
				/* $password='.' . $password . '.'; */
				$password=md5($password); 
			}
			$SQLQuery="update SecFrame_TLogin set TLogin_Username='$username',TLogin_Password='$password'," .
				"TLogin_Name='$name',TLogin_Email='$email',TLogin_Home='$home',TLogin_Work='$work',TLogin_Cell='$cell'," .
				"TLogin_Pager='$pager',TLogin_Address1='$address1',TLogin_Address2='$address2',TLogin_City='$city'," .
				"TLogin_State='$state',TLogin_Zip='$zip' where TLogin_ID=$id";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}	 
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_updateappperm             */
/*                                          */
/* Purpose:  Update TAppPerm given proper   */
/* information.                             */
/*                                          */
/********************************************/

function sec_updateappperm ($dbsocket, $id, $usergroup, $ugid, $allowaccess, $app_id, $priority) {

        $Results=0;
        if ( ( !embededsql($id) ) && ( !embededsql($usergroup) ) && ( !embededsql($ugid) ) && 
		( !embededsql($allowaccess) ) && ( !embededsql($app_id) ) && ( !embededsql($priority) ) && 
		( strval($id) > 0 ) && ( strval($ugid) > 0 ) && ( strval($app_id) > 0 ) ) {
                $id=stripslashes(pgdatatrim($id));
                $usergroup=stripslashes(pgdatatrim($usergroup));
                $ugid=stripslashes(pgdatatrim($ugid));
                $allowaccess=stripslashes(pgdatatrim($allowaccess));
                $app_id=stripslashes(pgdatatrim($app_id));
                $priority=stripslashes(pgdatatrim($priority));
                if ( ( strval($id) > 0 ) && ( sec_idexist($dbsocket,"SecFrame_TAppPerm","TAppPerm_ID",$id) ) ) {
                        $SQLQuery="update SecFrame_TAppPerm set TAppPerm_UserGroup=$usergroup,TAppPerm_UGID=$ugid,TAppPerm_AllowAccess=$allowaccess,TApp_ID=$app_id,TAppPerm_Priority=$priority where TAppPerm_ID=$id";
                        $SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
                                die(pg_errormessage()."<BR>\n");
                        if ( $SQLQueryResults ) { $Results = 1 ; }
                        pg_freeresult($SQLQueryResults) or
                                die(pg_errormessage() . "<BR>\n");
                }
        }
        return($Results);
}

/********************************************/
/*                                          */
/* Function:  groupmemberdropdownbox        */
/*                                          */
/* Purpose:  provide a tool for drop down   */
/* boxes where member is either 1 or 0.  1  */
/* lists those users who are a member.  0   */
/* lists those users who aren't members.    */
/*                                          */
/********************************************/

function groupmemberdropdownbox ($dbsocket, $fieldname, $groupid, $member=1,$tabs=0, $cr=1, $br=0, $lines=1, $multi=0, $selected="") {
	$SQLQuery="select * from SecFrame_TLogin order by TLogin_Username";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines"; 
	if ( $multi ) { echo " multiple";}
	echo "><BR>\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			if (((sec_groupmember($dbsocket,$SQLQueryResultsObject->tlogin_id,$groupid)) && ($member)) ||  
				((! sec_groupmember($dbsocket,$SQLQueryResultsObject->tlogin_id,$groupid)) && (! $member))) {
				if ( $SQLQueryResultsObject->tlogin_id==$selected ) {
					echo tabs($tabs+1) . "<option selected value=$SQLQueryResultsObject->tlogin_id>" . 
					stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_username)) . "</option>\n";
				} else {
					echo tabs($tabs+1) . "<option value=$SQLQueryResultsObject->tlogin_id>" . 
					stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_username)) . "</option>\n";
				}
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
}

/********************************************/
/*                                          */
/* Function:  sec_updateapp                 */
/*                                          */
/* Purpose:  Update TApp given proper info  */
/*                                          */
/********************************************/

function sec_updateapp ($dbsocket, $id, $appname, $appdesc) {

	$Results=0;
	if ( ( !embededsql($id) ) && ( !embededsql($appname) ) && ( !embededsql($appdesc) ) ) {
		$id=stripslashes(pgdatatrim($id));
		$appname=stripslashes(pgdatatrim($appname));
		$appdesc=stripslashes(pgdatatrim($appdesc));
		if ( ( strval($id) > 0 ) && ( sec_idexist($dbsocket,"SecFrame_TApp","TApp_ID",$id) ) ) {
			$SQLQuery="update SecFrame_TApp set TApp_Name='$appname',TApp_Desc='$appdesc' where TApp_ID=$id";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or        
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_updatequeue               */
/*                                          */
/* Purpose:  Update TApp given proper info  */
/*                                          */
/********************************************/

function sec_updatequeue ($dbsocket, $id, $command="", $date="", $time="", $dateprocessed="", $timeprocessed="", $processed="", $data1="", $data2="" ) {

	$Results=0;
	if ( ( strval($id) > 0 ) && ( sec_idexist($dbsocket,"SecFrame_TQueue","TQueue_ID",$id) ) ) {
		$SQLQuery="update SecFrame_TQueue set ";
		$flag=0;
		if ( $command != "" ) { 
			$flag=1;
			$SQLQuery=$SQLQuery . "TQueue_Command='$command'";
		}
		if ( $date != "" ) {
			if ( $flag ) {
				$SQLQuery=$SQLQuery . ", TQueue_Date='$date'"; 
			} else {
				$flag=1;
				$SQLQuery=$SQLQuery . "TQueue_Date='$date'";
			}
		}
		if ( $time != "" ) {
			if ( $flag ) {
				$SQLQuery=$SQLQuery . ", TQueue_Time='$time'"; 
			} else {
				$flag=1;
				$SQLQuery=$SQLQuery . "TQueue_Time='$time'";
			}
		}
		if ( $dateprocessed != "" ) {
			if ( $flag ) {
				$SQLQuery=$SQLQuery . ", TQueue_DateProcessed='$dateprocessed'"; 
			} else {
				$flag=1;
				$SQLQuery=$SQLQuery . "TQueue_DateProcessed='$dateprocessed'";
			}
		}
		if ( $timeprocessed != "" ) {
			if ( $flag ) {
				$SQLQuery=$SQLQuery . ", TQueue_TimeProcessed='$timeprocessed'"; 
			} else {
				$flag=1;
				$SQLQuery=$SQLQuery . "TQueue_TimeProcessed='$timeprocessed'";
			}
		}
		if ( $processed != "" ) {
			if ( $flag ) {
				$SQLQuery=$SQLQuery . ", TQueue_Processed=$processed"; 
			} else {
				$flag=1;
				$SQLQuery=$SQLQuery . "TQueue_Processed=$processed";
			}
		}
		if ( $data1 != "" ) {
			if ( $flag ) {
				$SQLQuery=$SQLQuery . ", TQueue_Data1='$data1'"; 
			} else {
				$flag=1;
				$SQLQuery=$SQLQuery . "TQueue_Data1='$data1'";
			}
		}
		if ( $data2 != "" ) {
			if ( $flag ) {
				$SQLQuery=$SQLQuery . ", TQueue_Data2='$data2'"; 
			} else {
				$flag=1;
				$SQLQuery=$SQLQuery . "TQueue_Data2='$data2'";
			}
		}

		$SQLQuery = $SQLQuery . " where TQueue_ID=$id";

		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results = 1 ; }
		pg_freeresult($SQLQueryResults) or        
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_updategroupmembers        */
/*                                          */
/* Purpose:  Update TGroupMembers given     */
/* proper info                              */
/*                                          */
/********************************************/

function sec_updategroupmembers ($dbsocket,$groupmembersid,$userid,$groupid) {

	$Results=0;
	if ( ( !embededsql($userid) ) && ( !embededsql($groupid) ) ) {
		$userid=stripslashes(pgdatatrim($userid));
		$groupid=stripslashes(pgdatatrim($groupid));
		if ( ( strval($userid) > 0 ) && ( strval($groupid) > 0 ) ) {
			$SQLQuery="update SecFrame_TGroupMembers set TLogin_ID=$userid,TGroup_ID=$groupid where TGroupMembers_ID=$groupmembersid";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or        
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_updategroup               */
/*                                          */
/* Purpose:  Updaate TGroup given proper    */
/*                                          */
/********************************************/

function sec_updategroup ($dbsocket, $groupid, $groupname, $groupdesc) {

	$Results=0;
	if ( ( !embededsql($groupname) ) && ( !embededsql($groupdesc) ) && ( !embededsql($groupid) ) ) {
		$groupname=stripslashes(substr(pgdatatrim($groupname),0,30));
		$groupdesc=stripslashes(substr(pgdatatrim($groupdesc),0,80));
		$groupid=stripslashes(substr(pgdatatrim($groupid),0,80));
		if ( ( $groupname != "" ) && ( $groupdesc != "" ) && ( is_string($groupname) ) && 
			( is_string($groupdesc) ) && ( strval($groupid) > 0 ) && 
			( sec_idexist($dbsocket,"SecFrame_TGroup","TGroup_ID",$groupid) ) ) {
			$SQLQuery="update SecFrame_TGroup set TGroup_Name='$groupname',TGroup_Desc='$groupdesc' where TGroup_ID=$groupid";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or        
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  userdropdownbox               */
/*                                          */
/* Purpose:  Creates a HTML drop down box   */
/* of users in the database                 */
/*                                          */
/********************************************/

function userdropdownbox ($dbsocket, $fieldname, $tabs=0, $cr=1, $br=0, $lines=1, $selected="", $groupid="") {

/*
CREATE TABLE SecFrame_TGroupMembers (
  TGroupMembers_ID integer DEFAULT nextval('TGroupMembers_Seq'),
  TLogin_ID integer not null,
  TGroup_ID integer not null
*/

	if ( $groupid ) {
		$SQLQuery="select * from SecFrame_TLogin,SecFrame_TGroupMembers where SecFrame_TGroupMembers.TLogin_ID=SecFrame_TLogin.TLogin_ID and TGroup_ID=$groupid order by TLogin_Username";
	} else {
		$SQLQuery="select * from SecFrame_TLogin order by TLogin_Username";
	}
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines><BR>\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResultsObject->tlogin_id==$selected ) {
				echo tabs($tabs+1) . "<option selected value=$SQLQueryResultsObject->tlogin_id>" . stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_username)) . "</option>\n";
			} else {
				echo tabs($tabs+1) . "<option value=$SQLQueryResultsObject->tlogin_id>" . stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_username)) . "</option>\n";
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
}

/********************************************/
/*                                          */
/* Function:  appdropdownbox                */
/*                                          */
/* Purpose:  Create a HTML drop down box    */
/* of Applications listed in the database   */
/*                                          */
/********************************************/

function appdropdownbox ($dbsocket, $fieldname, $tabs=0, $cr=1, $br=0, $lines=1, $selected="") {

	$SQLQuery="select * from SecFrame_TApp order by TApp_Name";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines><BR>\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResultsObject->tapp_id==$selected ) {
				echo tabs($tabs+1) . "<option selected value=$SQLQueryResultsObject->tapp_id>" . stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_name)) . "</option>\n";
			} else {
				echo tabs($tabs+1) . "<option value=$SQLQueryResultsObject->tapp_id>" . stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_name)) . "</option>\n";
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
}

/********************************************/
/*                                          */
/* Function:  groupdropdownbox              */
/*                                          */
/* Purpose:  Use these functions when doing */
/* any work with drop down boxes.           */
/*                                          */
/********************************************/ 

function groupdropdownbox ($dbsocket, $fieldname, $tabs=0, $cr=1, $br=0, $lines=1, $selected="") {

	$SQLQuery="select * from SecFrame_TGroup order by TGroup_Name";
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	echo tabs($tabs) . "<select name=$fieldname size=$lines><BR>\n";
	if ( $SQLNumRows > 0 ) {
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResultsObject->tgroup_id==$selected ) {
				echo tabs($tabs+1) . "<option selected value=$SQLQueryResultsObject->tgroup_id>" . stripslashes(pgdatatrim($SQLQueryResultsObject->tgroup_name)) . "</option>\n";
			} else {
				echo tabs($tabs+1) . "<option value=$SQLQueryResultsObject->tgroup_id>" . stripslashes(pgdatatrim($SQLQueryResultsObject->tgroup_name)) . "</option>\n";
			}
		}
	}
	echo tabs($tabs) . "</select>";
	crbr($cr,$br);
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
}

/********************************************/
/*                                          */
/* Function:  sec_accessallowed             */
/*                                          */
/* Purpose:  This does the evaluation of the*/
/* security creditials and returns either 1 */
/* or 0.                                    */
/*                                          */
/********************************************/

function sec_accessallowed($dbsocket,$userid,$appid) {

	$Results=0;
	if ( ( !embededsql($userid) ) && ( !embededsql($appid) ) ) {
		$userid=stripslashes(pgdatatrim($userid));
		$appid=stripslashes(pgdatatrim($appid));
		if ( ( strval($userid) > 0 ) && ( strval($appid) > 0 ) ) {
			$SQLQuery="select * from SecFrame_TAppPerm where TApp_ID=$appid order by TAppPerm_Priority";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
			if ( $SQLNumRows > 0 ) {
				for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
					$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
						die(pg_errormessage()."<BR>\n");
					$userorgroup=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_usergroup));
					$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_ugid));
					$allowaccess=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_allowaccess));
					if ( ( $userorgroup == 1 ) && ( $id == $userid ) ) { $Results = $allowaccess ; }
					if ( $userorgroup == 2 ) {
						if ( sec_groupmember($dbsocket,$userid,$id) ) { $Results = $allowaccess ; } 
					}
				}
			}
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_dbconnect               */
/*                                        */
/* Purpose:  Be a simple, single-point-of */
/* administration for controlling the db  */
/* user for the security framework at     */
/* connection time                        */
/*                                        */
/******************************************/

function sec_dbconnect() {

        $host = "127.0.0.1";
        $dbsocket = pg_connect("host=$host dbname=".SECDB." user=secframe password='".SECPASS."'") or
                die(pg_errormessage()."<BR>\n");
        return($dbsocket);
}

/******************************************/
/*                                        */
/* Function:  sec_groupmember             */
/*                                        */
/* Purpose:  Check if $userid is member   */
/* group $groupid.  If so, return # > 0   */
/* else return 0                          */
/*                                        */
/******************************************/

function sec_groupmember($dbsocket,$userid,$groupid) {

	$SQLNumRows = 0;
	if ( ( !embededsql($groupid) ) && ( !embededsql($userid) ) && ( $groupid != "" ) && ( $userid != "" ) ) {
		$groupid=stripslashes(pgdatatrim($groupid));
		$userid=stripslashes(pgdatatrim($userid));
		$SQLQuery="select TGroupMembers_ID from SecFrame_TGroupMembers where TLogin_ID=$userid and TGroup_ID=$groupid"; 
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($SQLNumRows);
}

/******************************************/
/*                                        */
/* Function:  sec_groupname               */
/*                                        */
/* Purpose:  Return the name of the group */
/* with the given id of $groupid          */
/*                                        */
/******************************************/

function sec_groupname($dbsocket,$groupid) {

	$Results="";
	if ( !embededsql($groupid) ) {
		$groupid=stripslashes(pgdatatrim($groupid));
		$Results=stripslashes(pgdatatrim(relatedata ($dbsocket,'SecFrame_TGroup','TGroup_Name',"TGroup_ID=$groupid")));
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_appnametoid               */
/*                                          */
/* Purpose:  Provide the ID of the given    */
/* TApp_Name                                */
/*                                          */
/********************************************/

function sec_appnametoid($dbsocket,$appname) {

	$Results="";
	if ( !embededsql($appname) ) {
		$appname=stripslashes(pgdatatrim($appname));
		$Results=stripslashes(pgdatatrim(relatedata ($dbsocket,'SecFrame_TApp','TApp_ID',"TApp_Name='$appname'")));
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_appname                   */
/*                                          */
/* Purpose:  Provide the text name of the   */
/* given TApp_ID                            */
/*                                          */
/********************************************/

function sec_appname($dbsocket,$appid) {

	$Results="";
	if ( !embededsql($appid) ) {
		$appid=stripslashes(pgdatatrim($appid));
		$Results=stripslashes(pgdatatrim(relatedata ($dbsocket,'SecFrame_TApp','TApp_Name',"TApp_ID=$appid")));
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_usernametoid              */
/*                                          */
/* Purpose:  Provide the TLogin_ID of the   */
/* given TLogin_Username                    */
/*                                          */
/********************************************/

function sec_usernametoid($dbsocket,$username) {

	$Results="";
	if ( !embededsql($username) ) {
		$username=stripslashes(pgdatatrim($username));
		$Results=stripslashes(pgdatatrim(relatedata ($dbsocket,'SecFrame_TLogin','TLogin_ID',"TLogin_Username='$username'")));
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_groupnametoid             */
/*                                          */
/* Purpose:  Provide the TGroup_ID of the   */
/* given TGroup_Name                        */
/*                                          */
/********************************************/

function sec_groupnametoid($dbsocket,$groupname) {

	$Results="";
	if ( !embededsql($groupname) ) {
		$groupname=stripslashes(pgdatatrim($groupname));
		$Results=stripslashes(pgdatatrim(relatedata ($dbsocket,'SecFrame_TGroup','TGroup_ID',"TGroup_Name='$groupname'")));
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_username                */
/*                                        */
/* Purpose:  Return the name of the user  */
/* with the given id of $userid           */
/*                                        */
/******************************************/

function sec_username($dbsocket,$userid) {

	$Results="";
	if ( !embededsql($userid) ) {
		$userid=stripslashes(pgdatatrim($userid));
		$Results=stripslashes(pgdatatrim(relatedata ($dbsocket,'SecFrame_TLogin','TLogin_Username',"TLogin_ID=$userid")));
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_delid                   */
/*                                        */
/* Purpose:  delete the row from          */
/* $tablename where the $idname equals    */
/* $id.                                   */
/*                                        */
/******************************************/

function sec_delid($dbsocket,$tablename,$idname,$id) {

	$Results=0;	
	if ( ( !embededsql($tablename) ) && ( !embededsql($idname) ) && ( !embededsql($id) ) ) {
		$tablename=stripslashes(pgdatatrim($tablename));
		$idname=stripslashes(pgdatatrim($idname));
		$id=stripslashes(pgdatatrim($id));
		if (sec_idexist($dbsocket,$tablename,$idname,$id)) { 
			$SQLQuery="delete from $tablename where $idname=$id";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_idexist                 */
/*                                        */
/* Purpose:  report wether $id exists in  */
/* $tablename.                            */
/*                                        */
/******************************************/

function sec_idexist($dbsocket,$tablename,$idname,$id) {

        $SQLNumRows = 0;
	if ( ( !embededsql($tablename) ) && ( !embededsql($idname) ) && ( !embededsql($id) ) ) {
		$tablename=stripslashes(pgdatatrim($tablename));
		$idname=stripslashes(pgdatatrim($idname));
		$id=stripslashes(pgdatatrim($id));
		if ( ( is_string($idname) ) && ( is_string($tablename) ) ) { 
			$SQLQuery="select $idname from $tablename where $idname=$id";
                	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or                
                	        die(pg_errormessage()."<BR>\n");          
                	$SQLNumRows = pg_numrows($SQLQueryResults);
               	 	pg_freeresult($SQLQueryResults) or         
                 	       die(pg_errormessage() . "<BR>\n");
		}
        }
        return($SQLNumRows);
}                           

/******************************************/
/*                                        */
/* Function:  sec_addgroup                */
/*                                        */
/* Purpose:  Add a group to the system.   */
/* If successful, the system returns 1    */
/* else the system returns 0.  The        */
/* function also boundary checks the new  */
/* data down to the SQL defined limits of */
/* the appropriate fields.                */
/*                                        */
/******************************************/

function sec_addgroup($dbsocket,$groupname,$groupdesc) {

	$Results=0;
	if ( ( !embededsql($groupname) ) && ( !embededsql($groupdesc) ) ) {
		$groupname=stripslashes(substr(pgdatatrim($groupname),0,30));	
		$groupdesc=stripslashes(substr(pgdatatrim($groupdesc),0,80));	
		if ( ( $groupname != "" ) && ( $groupdesc != "" ) && ( is_string($groupname) ) && ( is_string($groupdesc) ) ) {
			$SQLQuery="insert into SecFrame_TGroup (TGroup_Name,TGroup_Desc) values ('$groupname','$groupdesc')";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_addappperm              */
/*                                        */
/* Purpose:  Add app-permission assoc.    */
/* If successful, the system returns 1    */
/* else the system returns 0.  The        */
/* function also boundary checks the new  */
/* data down to the SQL defined limits of */
/* the appropriate fields.                */
/*                                        */
/******************************************/
                                           
function sec_addappperm($dbsocket,$usergroup,$ugid,$allowaccess,$appid,$priority) {

	$Results=0;
	if ( ( !embededsql($usergroup) ) && ( !embededsql($ugid) ) && 
		( !embededsql($allowaccess) ) && ( !embededsql($appid) ) &&
		( !embededsql($priority) ) ) {
		$usergroup=stripslashes(pgdatatrim($usergroup));
		$ugid=stripslashes(pgdatatrim($ugid));
		$allowaccess=stripslashes(pgdatatrim($allowaccess));
		$appid=stripslashes(pgdatatrim($appid));
		$priority=stripslashes(pgdatatrim($priority));
		if ( ( strval($appid) > 0 ) && ( strval($ugid) > 0 ) &&
			( strval($allowaccess) >= 0 ) && ( strval($appid) > 0 ) && 
			( strval($priority) > 0 ) ) {
			$SQLQuery="insert into SecFrame_TAppPerm (TAppPerm_UserGroup,TAppPerm_UGID,TAppPerm_AllowAccess,TApp_ID,TAppPerm_Priority) values ($usergroup,$ugid,$allowaccess,$appid,$priority)";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_getpriority               */
/*                                          */
/* Purpose:  Given an TApp_ID, provide with */
/* the next highest AppID in the chain.     */
/*                                          */
/********************************************/

function sec_getpriority($dbsocket,$appid) {

	$Results=1;
	if ( ( !embededsql($appid) ) && ( sec_idexist($dbsocket,"SecFrame_TAppPerm","TApp_ID",$appid) ) ) {
		$SQLQuery="select TAppPerm_Priority from SecFrame_TAppPerm order by TAppPerm_Priority desc";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$Results=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_priority));
			$Results++;
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_addgroupmembers         */
/*                                        */
/* Purpose:  Add a group member to db.    */
/* If successful, the system returns 1    */
/* else the system returns 0.  The        */
/* function also boundary checks the new  */
/* data down to the SQL defined limits of */
/* the appropriate fields.                */
/*                                        */
/******************************************/
                                           
function sec_addgroupmembers($dbsocket,$userid,$groupid) {

	$Results=0;
	if ( ( !embededsql($groupid) ) && ( !embededsql($userid) ) ) {
		$userid=stripslashes(pgdatatrim($userid));
		$groupid=stripslashes(pgdatatrim($groupid));
		if ( ( strval($groupid) > 0 ) && ( strval($userid) > 0 ) ) {
			$SQLQuery="insert into SecFrame_TGroupMembers (TLogin_ID,TGroup_ID) values ($userid,$groupid)";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_dropqueue                 */
/*                                          */
/* Purpose:  Remove commands from the queue */
/*                                          */
/********************************************/

function sec_dropqueue($dbsocket,$id) {

	$Results=0;
	if ( ( strval($id) > 0 ) && ( !embededsql($id) ) ) {
		$id=stripslashes(pgdatatrim($id));
		$SQLQuery="DELETE FROM SecFrame_TQueue where TQueue_ID=$id";	
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results = 1 ; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/********************************************/
/*                                          */
/* Function:  sec_dropgroupmembers          */
/*                                          */
/* Purpose:  This function removes a given  */
/* TLogin_ID/TGroup_ID assocation from the  */
/* TGroupMembers table                      */
/*                                          */
/********************************************/

function sec_dropgroupmembers($dbsocket,$userid,$groupid) {

	$Results=0;
	if ( ( !embededsql($groupid) ) && ( !embededsql($userid) ) ) {
		$userid=stripslashes(pgdatatrim($userid));
		$groupid=stripslashes(pgdatatrim($groupid));
		if ( ( strval($groupid) > 0 ) && ( strval($userid) > 0 ) ) {
			$SQLQuery="DELETE FROM SecFrame_TGroupMembers where TGroup_ID=$groupid and TLogin_ID=$userid";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_startbody               */
/*                                        */
/* Purpose:  set the default page         */
/* properites                             */
/*                                        */
/******************************************/

function sec_startbody($tabs=0) {

        echo tabs($tabs) . "<BODY bgcolor='#FFFFFF' text='#000000' LINK='#336699' VLINK='#9900FF' ALINK='#CC9933' background='images/tile.gif'><basefont size=5>";
}

/******************************************/
/*                                        */
/* Function:  sec_addapp                  */
/*                                        */
/* Purpose:  Add an app to the system.    */
/* If successful, the system returns 1    */
/* else the system returns 0.  The        */
/* function also boundary checks the new  */
/* data down to the SQL defined limits of */
/* the appropriate fields.                */
/*                                        */
/******************************************/

function sec_addapp($dbsocket,$appname,$appdesc) {

	$Results=0;
	if ( ( !embededsql($appname) ) && ( !embededsql($appdesc) ) ) {
		$appname=stripslashes(substr(pgdatatrim($appname),0,30));
		$appdesc=stripslashes(substr(pgdatatrim($appdesc),0,80));
		if ( ( $appname != "" ) && ( $appdesc != "" ) && ( is_string($appname) ) && ( is_string($appdesc) ) ) {
			$SQLQuery="insert into SecFrame_TApp (TApp_Name,TApp_Desc) values ('$appname','$appdesc')";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_addqueue                */
/*                                        */
/* Purpose:  Submit a job to be processed */
/* by the Queue runner.  The queue runner */
/* can do things like create local users, */
/* delete users, etc                      */
/*                                        */
/******************************************/

function sec_addqueue($dbsocket,$command="", $date="", $time="", $dateprocessed="", $timeprocessed="", $processed="", $data1="", $data2="" ) {

	$Results=0;
	if ( strlen(pgdatatrim($queuecommand)) > 0 ) {
		$SQLQuery="insert into SecFrame_TQueue (TQueue_Command,TQueue_Date,TQueue_Time,TQueue_DateProcessed,TQueue_TimeProcessed,TQueue_Processed,TQueue_Data1,TQueue_Data2) values ('$queuecommand','$date','$time','$dateprocessed','$timeprocessed','$processed','$queuedata1','$queuedata2')";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		if ( $SQLQueryResults ) { $Results = 1 ; }
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	return($Results);
}

/******************************************/
/*                                        */
/* Function:  sec_addlogin                */
/*                                        */
/* Purpose:  Add a login to the system.   */
/* If successful, the system returns 1    */
/* else the system returns 0.  The        */
/* function also boundary checks the new  */
/* data down to the SQL defined limits of */
/* the appropriate fields.                */
/*                                        */
/******************************************/

function sec_addlogin($dbsocket,$username,$password,$name,$email,$home,$work,$cell,$pager,$address1,$address2,$city,$state,$zip) {
	$Results=0;
	if ( ( !embededsql($username) ) &&
		( !embededsql($password) ) &&
		( !embededsql($name) ) &&
		( !embededsql($email) ) &&
		( !embededsql($home) ) &&
		( !embededsql($work) ) &&
		( !embededsql($cell) ) &&
		( !embededsql($pager) ) &&
		( !embededsql($address1) ) &&
		( !embededsql($address2) ) &&
		( !embededsql($city) ) &&
		( !embededsql($state) ) &&
		( !embededsql($zip) ) ) {
		$username=stripslashes(substr(pgdatatrim($username),0,128));	
		$password=stripslashes(substr(pgdatatrim($password),0,36));
		$name=stripslashes(substr(pgdatatrim($name),0,40));	
		$email=stripslashes(substr(pgdatatrim($email),0,40));	
		$home=stripslashes(substr(pgdatatrim($home),0,20));
		$cell=stripslashes(substr(pgdatatrim($cell),0,20));
		$work=stripslashes(substr(pgdatatrim($work),0,20));
		$pager=stripslashes(substr(pgdatatrim($pager),0,20));
		$address1=stripslashes(substr(pgdatatrim($address1),0,40));
		$address2=stripslashes(substr(pgdatatrim($address2),0,40));
		$city=stripslashes(substr(pgdatatrim($city),0,40));	
		$state=stripslashes(substr(pgdatatrim($state),0,40));	
		if ( ( $username != "" ) && ( is_string($username) ) &&
			( $password != "" ) && ( is_string($password) ) &&
			( $name != "" ) && ( is_string($name) ) &&
			( $password != "" ) && ( is_string($password) ) &&
			( is_string($email) ) &&
			( is_string($home) ) &&
			( is_string($work) ) &&
			( is_string($cell) ) &&
			( is_string($pager) ) &&
			( is_string($address1) ) &&
			( is_string($address2) ) &&
			( is_string($city) ) &&
			( is_string($state) ) &&
			( is_string($zip) ) ) { 
			if ( strlen($password) < 32 ) { $password=md5($password); }
			$SQLQuery="insert into SecFrame_TLogin (TLogin_Username,TLogin_Password," .
				"TLogin_Name,TLogin_Email,TLogin_Home,TLogin_Work,TLogin_Cell," .
				"TLogin_Pager,TLogin_Address1,TLogin_Address2,TLogin_City," .
				"TLogin_State,TLogin_Zip) values ('$username','$password','$name'," .
				"'$email','$home','$work','$cell','$pager','$address1','$address2'," .
				"'$city','$state','$zip')"; 
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $Results = 1 ; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}	 
	}
	return($Results);
}

%>
