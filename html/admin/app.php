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

	require_once('../config.php');

	$dbsocket=sec_dbconnect();

        $REMOTE_ID=sec_usernametoid($dbsocket,$REMOTE_USER);
        $ADMIN_ID=sec_groupnametoid($dbsocket,'Administrators');

        if ( ! sec_groupmember($dbsocket,$REMOTE_ID,$ADMIN_ID) ) {
                dbdisconnect($dbsocket);
                exit;
        }
	
	$PageTitle="Application Membership";
	do_header($PageTitle, 'adminapp');
	if ( ! isset($appfunction)) {
		$appfunction = 0;
	};
	if ( ( ( $action == "Modify") || ( $appfunction == 1 ) ) && ( isset($TApp_ID) ) ) {
		$appfunction = 1 ;
		echo "<B><H3>Modify Application</H3></B><BR>\n";
		if ( $SaveID == 1 ) {
			$Results = sec_updateapp ($dbsocket, $TApp_ID, $TApp_Name, $TApp_Desc);
			if ( $Results ) {
				echo "Save successfull<BR>\n";
			} else {
				echo "Save failed!<BR>\n";
			}
		}
		$SQLQuery="select * from SecFrame_TApp where TApp_ID=$TApp_ID";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows > 0 ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
				die(pg_errormessage()."<BR>\n");
			$TApp_Name = stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_name)); 
			$TApp_Desc = stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_desc)); 
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		} else {
			$TApp_Name="";
			$Tapp_Desc="";
		}
		openform("app.php","post",2,1,0);
		formfield("TApp_ID","Hidden",3,1,0,10,10,$TApp_ID);
		formfield("appfunction","Hidden",3,1,0,10,10,$appfunction);
		formfield("SaveID","Hidden",3,1,0,10,10,"1");
		echo "Application Name:  ";
		formfield("TApp_Name","TEXT",3,1,1,30,30,$TApp_Name); 
		echo "Application Description:  ";
		formfield("TApp_Desc","TEXT",3,1,1,30,80,$TApp_Desc);
		formsubmit("Save",3,1,0);
		formreset("Reset",3,1,1);
		closeform(1);
	}
	if ( ( ( $action == "Delete") || ( $appfunction == 2 ) ) && ( isset($TApp_ID) ) ) {
		$appfunction = 2;
		echo "<B><H3>Delete Application</H3></B><BR>\n";
		if ( $DeleteID == 1 ) {
			$ResultsApp = sec_delid($dbsocket,"SecFrame_TApp","TApp_ID",$TApp_ID);
			$ResultsAppPerm = sec_delid($dbsocket,"SecFrame_TAppPerm","TApp_ID",$TApp_ID); 
			if ( ( $ResultsApp ) && ( $ResultsAppPerm ) ) {
				echo "Delete successfull<BR>\n";
			} else {
				echo "Delete failed!<BR>\n";
			}
		} else {
			$SQLQuery="select * from SecFrame_TApp where TApp_ID=$TApp_ID";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
			if ( $SQLNumRows > 0 ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
					die(pg_errormessage()."<BR>\n");
				$TApp_Name = stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_name)); 
				$TApp_Desc = stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_desc)); 
				pg_freeresult($SQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
			} else {
				$TApp_Name="";
				$TApp_Desc="";
			}
			openform("app.php","post",2,1,0);
			formfield("TApp_ID","Hidden",3,1,0,10,10,$TApp_ID);
			formfield("appfunction","Hidden",3,1,0,10,10,$appfunction);
			/* formfield("DeleteID","Hidden",3,1,0,10,10,"1"); */
			echo "<font color=#FF0000 size=+2><B>Are you sure you want to delete $TApp_Desc?  ";
%>
		<input type=radio name=DeleteID value=1>Yes  
		<input type=radio name=DeleteID value=0 checked>No</font><b><BR>
<%
			formsubmit("Delete",3,1,0);
			formreset("Reset",3,1,1);
			closeform(1);
		}
	}
	if ( ( ( $action == "Adjust ACL") || ( $appfunction == 3 ) ) && ( isset($TApp_ID) ) && ( sec_idexist($dbsocket,"SecFrame_TApp","TApp_ID",$TApp_ID) ) ) {
		$appfunction = 3 ;
		if ( ( $action == "Up" ) && ( sec_idexist($dbsocket,"SecFrame_TAppPerm","TAppPerm_ID",$TAppPerm_ID) ) && 
			( sec_idexist($dbsocket,"SecFrame_TApp","TApp_ID",$TApp_ID) ) ) {
			$SQLQuery="select * from SecFrame_TAppPerm where TApp_ID=$TApp_ID order by TAppPerm_Priority";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
			if ( $SQLNumRows > 0 ) {
				for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
					$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
						die(pg_errormessage()."<BR>\n");
					$ACLID[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_id));
					$ACLUserGroup[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_usergroup));
					$ACLUGID[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_ugid));
					$ACLAllowAccess[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_allowaccess));
					$ACLAppID[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_id));
					$ACLPriority[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_priority));
					array_multisort($ACLPriority,$ACLID,$ACLUGID,$ACLUserGroup,$ACLAllowAccess,$ACLAppID);
				}
				$found=0;
				for ( $loop = $SQLNumRows ; $loop != 0 ; $loop-- ) {
					if ( $loop != 0 ) { 
						if ( $ACLID[$loop] == $TAppPerm_ID ) { $found=$loop; } 
					}
				}	
				if ( $found > 0 ) {
					$swap=$ACLID[$found];
					$ACLID[$found]=$ACLID[$found-1];
					$ACLID[$found-1]=$swap;

					$swap=$ACLUserGroup[$found];
					$ACLUserGroup[$found]=$ACLUserGroup[$found-1];
					$ACLUserGroup[$found-1]=$swap;

					$swap=$ACLUGID[$found];
					$ACLUGID[$found]=$ACLUGID[$found-1];
					$ACLUGID[$found-1]=$swap;

					$swap=$ACLAllowAccess[$found];
					$ACLAllowAccess[$found]=$ACLAllowAccess[$found-1];
					$ACLAllowAccess[$found-1]=$swap;

					$swap=$ACLAppID[$found];
					$ACLAppID[$found]=$ACLAppID[$found-1];
					$ACLAppID[$found-1]=$swap;

					/*$swap=$ACLPriority[$found];
					$ACLPriority[$found]=$ACLPriority[$found-1];
					$ACLPriority[$found-1]=$swap;*/

					array_multisort($ACLPriority,$ACLID,$ACLUGID,$ACLUserGroup,$ACLAllowAccess,$ACLAppID);
					for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
						sec_updateappperm ($dbsocket, $ACLID[$loop], $ACLUserGroup[$loop], 
							$ACLUGID[$loop], $ACLAllowAccess[$loop], $ACLAppID[$loop], $ACLPriority[$loop]);
					}
				}
			}
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
		if ( ( $action == "Down" ) && ( sec_idexist($dbsocket,"SecFrame_TAppPerm","TAppPerm_ID",$TAppPerm_ID) ) &&
			( sec_idexist($dbsocket,"SecFrame_TApp","TApp_ID",$TApp_ID) ) ) {
			$SQLQuery="select * from SecFrame_TAppPerm where TApp_ID=$TApp_ID order by TAppPerm_Priority";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
			if ( $SQLNumRows > 0 ) {
				for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
					$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
						die(pg_errormessage()."<BR>\n");
					$ACLID[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_id));
					$ACLUserGroup[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_usergroup));
					$ACLUGID[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_ugid));
					$ACLAllowAccess[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_allowaccess));
					$ACLAppID[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_id));
					$ACLPriority[$loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_priority));
					array_multisort($ACLPriority,$ACLID,$ACLUGID,$ACLUserGroup,$ACLAllowAccess,$ACLAppID);
				}
				$found=0;
				for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
					if ( $loop != $SQLNumRows ) { 
						if ( $ACLID[$loop] == $TAppPerm_ID ) { $found=$loop; } 
					}
				}	
				if ( $found < $SQLNumRows ) {
					$swap=$ACLID[$found];
					$ACLID[$found]=$ACLID[$found+1];
					$ACLID[$found+1]=$swap;

					$swap=$ACLUserGroup[$found];
					$ACLUserGroup[$found]=$ACLUserGroup[$found+1];
					$ACLUserGroup[$found+1]=$swap;

					$swap=$ACLUGID[$found];
					$ACLUGID[$found]=$ACLUGID[$found+1];
					$ACLUGID[$found+1]=$swap;

					$swap=$ACLAllowAccess[$found];
					$ACLAllowAccess[$found]=$ACLAllowAccess[$found+1];
					$ACLAllowAccess[$found+1]=$swap;

					$swap=$ACLAppID[$found];
					$ACLAppID[$found]=$ACLAppID[$found+1];
					$ACLAppID[$found+1]=$swap;

					/*$swap=$ACLPriority[$found];
					$ACLPriority[$found]=$ACLPriority[$found+1];
					$ACLPriority[$found+1]=$swap;*/

					array_multisort($ACLPriority,$ACLID,$ACLUGID,$ACLUserGroup,$ACLAllowAccess,$ACLAppID);
					for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
						sec_updateappperm ($dbsocket, $ACLID[$loop], $ACLUserGroup[$loop], 
							$ACLUGID[$loop], $ACLAllowAccess[$loop], $ACLAppID[$loop], $ACLPriority[$loop]);
					}
				}
			}
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
		if ( $action == "Save" ) {
			if ( $add == "user" ) {
				$usergroup=1;
				$ugid=$TLogin_ID;
			} else {
				$usergroup=2;
				$ugid=$TGroup_ID;
			}
			$priority= sec_getpriority($dbsocket,$TApp_ID);
			$Results = sec_addappperm($dbsocket,$usergroup,$ugid,$AllowAccess,$TApp_ID,$priority); 
                        if ( $Results ) {
                                echo "Add successfull<BR>\n";
                        } else {
                                echo "Add failed!<BR>\n";
                        }
		}
		if ( ( $action == "Remove" ) && ( isset($TAppPerm_ID) ) && ( sec_idexist($dbsocket,"SecFrame_TAppPerm","TAppPerm_ID",$TAppPerm_ID) ) ) {
			$Results = sec_delid($dbsocket,"SecFrame_TAppPerm","TAppPerm_ID",$TAppPerm_ID);
			if ( $Results ) {
				echo "ACL removal successfull<BR>\n";
			} else {
				echo "ACL removal failed!<BR>\n";
			}
		}
		openform("app.php","post",2,1,0);
		formfield("TApp_ID","Hidden",3,1,0,10,10,$TApp_ID);
		formfield("appfunction","Hidden",3,1,0,10,10,$appfunction);
		echo "<font size=+2>Access-List:  " . sec_appname($dbsocket,$TApp_ID) . "</font><BR>\n";
		echo "<table border=2><tr><td><B>User/Group</B></td><td><B>User/Group Name</B></TD><TD><B>Permit/Deny</B></TD><TD><B>Save or Reset</B></td></tr>\n";
		echo "<tr><td>Group:  <input type=radio name=add value=group checked>  </TD><TD ROWSPAN=2>Group:  ";
		groupdropdownbox ($dbsocket,"TGroup_ID",3,1,1,1,"");
		echo "<BR>\nUser:  ";
		userdropdownbox ($dbsocket,"TLogin_ID",3,1,1,1,"");
		echo "</td><td ROWSPAN=2>Action:  ";
		echo "<select name=AllowAccess size=1>\n";
		echo "<option value=1>PERMIT</option>\n";
		echo "<option value=0>DENY</option>\n";
		echo "</select>\n";
		echo "</TD><TD ROWSPAN=2>";
		formsubmit("Save",3,1,0);
		formreset("Reset",3,1,1);
		echo "</TD></TR>\n";
		echo "<TR><TD>User:  <input type=radio name=add value=user>  "; 
		echo "</TD></TR></TABLE>\n";

		$SQLQuery="select * from SecFrame_TAppPerm where TApp_ID=$TApp_ID order by TAppPerm_Priority";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows > 0 ) {
			echo "<TABLE border=2>";
			echo "<TR><TD><B>ACL Entry</B></TD><TD><B>User/Group Name</B></TD><TD><B>User/Group</B></TD><TD><B>Permit/Deny</B></TD></TR>\n";
			for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$tappperm_id=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_id));
				$tapp_id=stripslashes(pgdatatrim($SQLQueryResultsObject->tapp_id));
				$tappperm_ugid=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_ugid));
				$tappperm_usergroup=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_usergroup));
				$tappperm_allowaccess=stripslashes(pgdatatrim($SQLQueryResultsObject->tappperm_allowaccess));
				echo "<TR><TD align=center><input type=radio name=TAppPerm_ID value=$tappperm_id></TD>";
				if ( $tappperm_usergroup == 1 ) {
					echo "<TD>" . sec_username($dbsocket,$tappperm_ugid) . "</td><td>User</td>";
				} else {
					echo "<TD>" . sec_groupname($dbsocket,$tappperm_ugid) . "</td><td>Group</td>";
				}
				if ( $tappperm_allowaccess ) {
					echo "<TD>Permit</TD></TR>\n";
				} else {
					 echo "<TD>Deny</TD></TR>\n";
				}		
			}
			echo "<TR><TD>";
			formsubmit("Remove",3,1,0);
			echo "</TD><TD>";
			formsubmit("Up",3,1,0);
			formsubmit("Down",3,1,1);
			echo "</TD></TR>";
			echo "</table>\n";
			pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		}
		closeform(1);
	}
	if ( $appfunction == 0 ) {
		echo "<B><H3>Add an Application</H3></B><BR>\n";
		if ( $SaveID == 1 ) {
			$Results = sec_addapp($dbsocket,$TApp_Name,$TApp_Desc);
			if ( $Results ) {
				echo "Add successfull<BR>\n";
			} else {
				echo "Add failed!<BR>\n";
			}
		} else {
			openform("app.php","post",2,1,0);
			formfield("SaveID","Hidden",3,1,0,10,10,"1");
			echo "Application Name:  ";
			formfield("TApp_Name","TEXT",3,1,1,30,30,"");
			echo "Application Description:  ";
			formfield("TApp_Desc","TEXT",3,1,1,30,80,"");
			formsubmit("Save",3,1,0);
			formreset("Reset",3,1,1);
			closeform(1);
		}
	}
	do_footer();
	dbdisconnect($dbsocket);
%>
