<?php
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


	require_once('../config.php');

	$dbsocket=sec_dbconnect();

        $REMOTE_ID=sec_usernametoid($dbsocket,$_SERVER['REMOTE_USER']);
        $ADMIN_ID=sec_groupnametoid($dbsocket,'Administrators');
        if ( ! sec_groupmember($dbsocket,$REMOTE_ID,$ADMIN_ID) ) {
                dbdisconnect($dbsocket);
                exit;
        }
	
	$PageTitle="Group Membership";
	do_header($PageTitle, 'admingroup');
	if (! isset($groupfunction)) {
		$groupfunction = 0;
	}
	if ( ( ( $_POST['action'] == "Modify" ) || ( $groupfunction == 1 ) ) && ( isset($TGroup_ID) ) ) {
		$groupfunction = 1 ; 
		echo "<B><H3>Modify Group</H3></B><BR>\n";
		if ( $SaveID == 1 ) {
			$Results = sec_updategroup ($dbsocket, $TGroup_ID, $TGroup_Name, $TGroup_Desc);
			if ( $Results ) {
				echo "Save successfull<BR>\n";
			} else {
				echo "Save failed!<BR>\n";
			}
		}
		$SQLQuery="select * from SecFrame_TGroup where TGroup_ID=$TGroup_ID";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows > 0 ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
				die(pg_errormessage()."<BR>\n");
			$TGroup_Name = stripslashes(pgdatatrim($SQLQueryResultsObject->tgroup_name)); 
			$TGroup_Desc = stripslashes(pgdatatrim($SQLQueryResultsObject->tgroup_desc)); 
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		} else {
			$TGroup_Name="";
			$TGroup_Desc="";
		}
		openform("group.php","post",2,1,0);
		formfield("TGroup_ID","Hidden",3,1,0,10,10,$TGroup_ID);
		formfield("groupfunction","Hidden",3,1,0,10,10,$groupfunction);
		formfield("SaveID","Hidden",3,1,0,10,10,"1");
		echo "Group Name:  ";
		formfield("TGroup_Name","TEXT",3,1,1,30,30,$TGroup_Name); 
		echo "Group Description:  ";
		formfield("TGroup_Desc","TEXT",3,1,1,30,80,$TGroup_Desc);
		formsubmit("Save",3,1,0);
		formreset("Reset",3,1,1);
		closeform(1);
	}
	if ( ( ( $_POST['action'] == "Delete" ) || ( $groupfunction == 2 ) ) && ( isset($TGroup_ID) ) ) {
		$groupfunction = 2 ;
		echo "<B><H3>Delete Group</H3></B><BR>\n";
		if ( $DeleteID == 1 ) {
			$Results = sec_delid($dbsocket,"SecFrame_TGroup","TGroup_ID",$TGroup_ID);
			$ResultsGroupMembers = sec_delid($dbsocket,"SecFrame_TGroupMembers","TGroup_ID",$TGroup_ID);
			if ( ( $Results ) && ( $ResultsGroupMembers ) ) {
				$SQLQuery="delete from SecFrame_TAppPerm where TAppPerm_UserGroup=2 and TAppPerm_UGID=$TGroup_ID";
				$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
					die(pg_errormessage()."<BR>\n");
				pg_freeresult($SQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
				echo "Delete successfull<BR>\n";
			} else {
				echo "Delete failed!<BR>\n";
			}
		} else {
			$SQLQuery="select * from SecFrame_TGroup where TGroup_ID=$TGroup_ID";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
			if ( $SQLNumRows > 0 ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
					die(pg_errormessage()."<BR>\n");
				$TGroup_Name = stripslashes(pgdatatrim($SQLQueryResultsObject->tgroup_name)); 
				$TGroup_Desc = stripslashes(pgdatatrim($SQLQueryResultsObject->tgroup_desc)); 
				pg_freeresult($SQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
			} else {
				$TGroup_Name="";
				$TGroup_Desc="";
			}
			openform("group.php","post",2,1,0);
			formfield("TGroup_ID","Hidden",3,1,0,10,10,$TGroup_ID);
			formfield("groupfunction","Hidden",3,1,0,10,10,$groupfunction);
			/* formfield("DeleteID","Hidden",3,1,0,10,10,"1"); */
			echo "<font color=#FF0000 size=+2><B>Are you sure you want to delete $TGroup_Desc?  ";
?>
		<input type=radio name=DeleteID value=1>Yes  
		<input type=radio name=DeleteID value=0 checked>No</b></font><BR>
<?php
			formsubmit("Save",3,1,0);
			formreset("Reset",3,1,1);
			closeform(1);
		}
	}
	if ( ( ( $_POST['action'] == "Adjust Membership" ) || ( $groupfunction == 3 ) ) && ( isset($TGroup_ID) ) && ( sec_idexist($dbsocket,"SecFrame_TGroup","TGroup_ID",$TGroup_ID) ) ) {
		$groupfunction = 3;
		echo "<B><H3>Modify Membership</H3></B><BR>\n";
		if ( $_POST['action'] == "Remove" ) {
			if ( count($TLogin_ID) != 0 ) {
				for ( $loop = 0 ; $loop != count($TLogin_ID) ; $loop++ ) {
					$Results = sec_dropgroupmembers($dbsocket,$TLogin_ID[$loop],$TGroup_ID);
				}
			}
		}
		if ( $_POST['action'] == "Add" ) {
			if ( count($TLogin_ID) != 0 ) {
				for ( $loop = 0 ; $loop != count($TLogin_ID) ; $loop++ ) {
					$Results = sec_addgroupmembers($dbsocket,$TLogin_ID[$loop],$TGroup_ID);
				}
			}
		}
		openform("group.php","post",2,1,0);
		formfield("TGroup_ID","Hidden",3,1,0,10,10,$TGroup_ID);
		formfield("groupfunction","Hidden",3,1,0,10,10,$groupfunction);
		echo "<B><font size=+1>Group:  " . sec_groupname($dbsocket,$TGroup_ID) . "</B><BR>\n";
		echo tabs(2) . "<TABLE border=2>\n<TR><TD>\n";
		echo "<B><U><font color=#FF0000>Non-Members:</FONT></u></B></TD><TD><B><U><FONT Color=#00FF00>Members</FONT></U></B></TD></TR>\n<TR><TD>";
		groupmemberdropdownbox ($dbsocket,"TLogin_ID[]",$TGroup_ID,0,0,1,1,5,1);
		echo tabs(2) . "</TD><TD>\n"; 
		groupmemberdropdownbox ($dbsocket,"TLogin_ID[]",$TGroup_ID,1,0,1,1,5,1);
		echo tabs(2) . "</TD></TR>\n<TR><TD>"; 
		formsubmit("Add",3,1,0);
		echo tabs(2) . ">>> </TD><TD> <<<";
		formsubmit("Remove",3,1,0);
		echo tabs(2) . "</TD><TR></table>\n";
		closeform(1);
	}
	if ( $groupfunction == 0 ) {
		echo "<B><H3>Add a Group</H3></B><BR>\n";
		if ( $SaveID == 1 ) {
			$Results = sec_addgroup($dbsocket,$TGroup_Name,$TGroup_Desc);
			if ( $Results ) {
				echo "Add successfull<BR>\n";
			} else {
				echo "Add failed!<BR>\n";
			}
		} else {
			openform("group.php","post",2,1,0);
			formfield("SaveID","Hidden",3,1,0,10,10,"1");
			echo "Group Name:  ";
			formfield("TGroup_Name","TEXT",3,1,1,30,30,"");
			echo "Group Description:  ";
			formfield("TGroup_Desc","TEXT",3,1,1,30,80,"");
			formsubmit("Save",3,1,0);
			formreset("Reset",3,1,1);
			closeform(1);
		}
	}
			
			
	do_footer();			
	dbdisconnect($dbsocket);
?>
