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
	
	$PageTitle="User Membership";
	do_header($PageTitle, 'adminuser');

	if ( ! isset($userfunction)) {
		$userfunction = 0;
	}
	if ( ( ( $userfunction == 1 ) || ( $action == "Modify" ) ) && ( isset($TLogin_ID) ) ) {
		$userfunction = 1 ; 
		echo "<BR><B><FONT SIZE=+1>Modify User</FONT></B><BR><BR>\n";
		if ( isset($SaveID) && $SaveID == 1 ) {
			$reason="";
			if ( $TLogin_Password == $TLogin_Password2 ) {
				if ( strlen($TLogin_Password) >= 8 ) {
					if ( sec_verifypassword($TLogin_Password) || ( strlen($TLogin_Password) > 31 ) ) {
						
						$Results = sec_updatelogin ($dbsocket,$TLogin_ID,$TLogin_Username,$TLogin_Password,
							$TLogin_Name,$TLogin_Email,$TLogin_Home,$TLogin_Work,$TLogin_Cell,$TLogin_Pager,
							$TLogin_Address1,$TLogin_Address2,$TLogin_City,$TLogin_State,$TLogin_Zip);
					} else {
						$reason = "<B>Password requires a mix of uppercase or lowercase letters with numbers or symbols</B>"; 
					}
				} else {
					$reason = "<B>Password not log enough!</B>";
				}
			} else {
				$reason = "<B>Password mismatch!</B>";
			} 
			if ( isset($Results) ) {
				echo "Save successfull<BR>\n";
			} else {
				echo "<font color=#FF0000 size=+2><B>Save failed!</B></FONT>  $reason<BR>\n";
			}
		}
		$SQLQuery="select * from SecFrame_TLogin where TLogin_ID=$TLogin_ID";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows > 0 ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
				die(pg_errormessage()."<BR>\n");
			$TLogin_Username = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_username)); 
			$TLogin_Password = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_password)); 
			$TLogin_Name = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_name)); 
			$TLogin_Email = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_email)); 
			$TLogin_Work = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_work)); 
			$TLogin_Home = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_home)); 
			$TLogin_Cell = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_cell)); 
			$TLogin_Pager = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_pager)); 
			$TLogin_Address1 = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_address1)); 
			$TLogin_Address2 = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_address2)); 
			$TLogin_City = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_city)); 
			$TLogin_State = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_state)); 
			$TLogin_Zip = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_zip)); 
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		} else {
			$TLogin_Username="";
			$TLogin_Password="";
			$TLogin_Name="";
			$TLogin_Email="";
			$TLogin_Work="";
			$TLogin_Home="";
			$TLogin_Cell="";
			$TLogin_Pager="";
			$TLogin_Address1="";
			$TLogin_Address2="";
			$TLogin_City="";
			$TLogin_State="";
			$TLogin_Zip="";
		}
		openform("user.php","post",2,1,0);
		formfield("TLogin_ID","Hidden",3,1,0,10,10,$TLogin_ID);
		formfield("userfunction","Hidden",3,1,0,10,10,$userfunction);
		formfield("SaveID","Hidden",3,1,0,10,10,"1");
		echo "<TABLE border=2 COLS=2 WIDTH=100%><TR><TD>";
		echo "<font color=#FF0000 size=+2><B>*</B></FONT>User Name:  ";
		formfield("TLogin_Username","TEXT",3,1,1,16,16,$TLogin_Username); 
		echo "</TD><TD WIDTH><font color=#FF0000 size=+2><B>*</B></FONT>Password:  ";
		formfield("TLogin_Password","Password",3,1,1,16,32,$TLogin_Password); 
		echo "  <font color=#FF0000 size=+2><B>*</B></FONT>Confirm Password:  ";
		formfield("TLogin_Password2","Password",3,1,1,16,32,$TLogin_Password); 
		echo "</TD></TR><TR><TD><font color=#FF0000 size=+2><B>*</B></FONT>Name:  ";
		formfield("TLogin_Name","TEXT",3,1,1,40,128,$TLogin_Name); 
		echo "</TD><TD><font color=#FF0000 size=+2><B>*</B></FONT>Email:";
		formfield("TLogin_Email","TEXT",3,1,1,30,40,$TLogin_Email); 
		echo "</TD></TR><TR><TD>Home Phone:  ";
		formfield("TLogin_Home","TEXT",3,1,1,20,20,$TLogin_Home); 
		echo "</TD><TD>Cell Phone:  ";
		formfield("TLogin_Cell","TEXT",3,1,1,20,20,$TLogin_Cell); 
		echo "</TD></TR><TR><TD>Work Phone:  ";
		formfield("TLogin_Work","TEXT",3,1,1,20,20,$TLogin_Work); 
		echo "</TD><TD>Pager:  ";
		formfield("TLogin_Pager","TEXT",3,1,1,20,20,$TLogin_Pager); 
		echo "</TD></TR><TR><TD COLSPAN=2>Address 1:  ";
		formfield("TLogin_Address1","TEXT",3,1,1,40,40,$TLogin_Address1); 
		echo "Address 2:  ";
		formfield("TLogin_Address2","TEXT",3,1,1,40,40,$TLogin_Address2); 
		echo "City:  ";
		formfield("TLogin_City","TEXT",3,0,0,40,40,$TLogin_City); 
		echo "  State:  ";
		formfield("TLogin_State","TEXT",3,0,0,2,2,$TLogin_State); 
		echo "  Zip:  ";
		formfield("TLogin_Zip","TEXT",3,1,1,12,12,$TLogin_Zip); 
		echo "</TD></TR><TR><TD>";
		formsubmit("Save",3,1,0);
		echo "</TD><TD>";
		formreset("Reset",3,1,1);
		echo "</TD></TR></TABLE><BR>\n<font color=#FF0000 size=+2><B>* - Denotes required field</B></font><BR> ";
		closeform(1);
	}
	if ( ( ( $userfunction == 2 ) || ( $action == "Delete" ) ) && ( isset($TLogin_ID) ) ) {
		$userfunction = 2;
		echo "<B><H3>Delete User</H3></B><BR>\n";
		if ( $DeleteID == 1 ) {
			$Results = sec_delid($dbsocket,"SecFrame_TLogin","TLogin_ID",$TLogin_ID);
			$ResultsGroupMembers = sec_delid($dbsocket,"SecFrame_TGroupMembers","TLogin_ID",$TLogin_ID);
			if ( ( $Results ) && ( $ResultsGroupMembers ) ) {
				$SQLQuery="delete from SecFrame_TAppPerm where TAppPerm_UserGroup=1 and TAppPerm_UGID=$TLogin_ID";
				$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
					die(pg_errormessage()."<BR>\n");
				pg_freeresult($SQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
				echo "Delete successfull<BR>\n";
			} else {
				echo "Delete failed!<BR>\n";
			}
		} else {
			$SQLQuery="select * from SecFrame_TLogin where TLogin_ID=$TLogin_ID";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
			if ( $SQLNumRows > 0 ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,0) or
					die(pg_errormessage()."<BR>\n");
				$TLogin_Name = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_name)); 
				$TLogin_Username = stripslashes(pgdatatrim($SQLQueryResultsObject->tlogin_username)); 
				pg_freeresult($SQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
			} else {
				$TLogin_Name="";
				$TLogin_Username="";
			}
			openform("user.php","post",2,1,0);
			formfield("TLogin_ID","Hidden",3,1,0,10,10,$TLogin_ID);
			formfield("userfunction","Hidden",3,1,0,10,10,$userfunction);
			echo "<font color=#FF0000 size=+2><B>Are you sure you want to delete $TLogin_Name?  ";
%>
		<input type=radio name=DeleteID value=1>Yes  
		<input type=radio name=DeleteID value=0 checked>No</B></FONT><BR>
<%
			formsubmit("Delete",3,1,0);
			formreset("Reset",3,1,1);
			closeform(1);
		}
	}
	if ($userfunction == 0 ) {
		echo "<B><H3>Add a User</H3></B><BR>\n";
		if ( isset($SaveID) && ($SaveID == 1) ) {
                        $reason="";
			$Results=0;
                        if ( $TLogin_Password == $TLogin_Password2 ) {
                                if ( strlen($TLogin_Password) >= 8 ) {
					if ( sec_verifypassword($TLogin_Password) ) {
						$Results = sec_addlogin($dbsocket,$TLogin_Username,$TLogin_Password,$TLogin_Name,
							$TLogin_Email,$TLogin_Home,$TLogin_Work,$TLogin_Cell,$TLogin_Pager,
							$TLogin_Address1,$TLogin_Address2,$TLogin_City,$TLogin_State,$TLogin_Zip);
						$TempTLogin_ID=sec_usernametoid($dbsocket,$TLogin_Username);
						$EVERYONEGROUP_ID=sec_groupnametoid($dbsocket,'Everyone'); 
						$Results2 = sec_addgroupmembers($dbsocket,$TempTLogin_ID,$EVERYONEGROUP_ID);
					} else {
						$reason = "<B>Password requires a mix of uppercase or lowercase letters with numbers or symbols</B>"; 
					}
                                } else {
                                        $reason = "<B>Password not log enough!</B>";
                                }
                        } else {
                                $reason = "<B>Password mismatch!</B>";
                        }
			if ( ( $Results ) && ( $Results2 ) ) {
				echo "Add successfull<BR>\n";
			} else {
				echo "<font color=#FF0000 size=+2><B>Add failed!</B></FONT>  $reason<BR>\n";
			}
		} else {
			openform("user.php","post",2,1,0);
			formfield("SaveID","Hidden",3,1,0,10,10,"1");
			echo "<TABLE border=2 COLS=2 WIDTH=100%><TR><TD>";
			echo "<font color=#FF0000 size=+2><B>*</B></FONT>User Name:  ";
			formfield("TLogin_Username","TEXT",3,1,1,16,16,""); 
			echo "</TD><TD><font color=#FF0000 size=+2><B>*</B></FONT>Password:  ";
			formfield("TLogin_Password","Password",3,1,1,16,32,""); 
			echo "  <font color=#FF0000 size=+2><B>*</B></FONT>Confirm Password:  ";
			formfield("TLogin_Password2","Password",3,1,1,16,32,""); 
			echo "</TD></TR><TR><TD><font color=#FF0000 size=+2><B>*</B></FONT>Name:  ";
			formfield("TLogin_Name","TEXT",3,1,1,40,40,""); 
			echo "</TD><TD><font color=#FF0000 size=+2><B>*</B></FONT>Email:  ";
			formfield("TLogin_Email","TEXT",3,1,1,40,40,""); 
			echo "</TD></TR><TR><TD>Home Phone:  ";
			formfield("TLogin_Home","TEXT",3,1,1,20,20,""); 
			echo "</TD><TD>Cell Phone:  ";
			formfield("TLogin_Cell","TEXT",3,1,1,20,20,""); 
			echo "</TD></TR><TR><TD>Work Phone:  ";
			formfield("TLogin_Work","TEXT",3,1,1,20,20,""); 
			echo "</TD><TD>Pager:  ";
			formfield("TLogin_Pager","TEXT",3,1,1,20,20,""); 
			echo "</TD></TR><TR><TD COLSPAN=2>Address 1:  ";
			formfield("TLogin_Address1","TEXT",3,1,1,40,40,""); 
			echo "Address 2:  ";
			formfield("TLogin_Address2","TEXT",3,1,1,40,40,""); 
			echo "City:  ";
			formfield("TLogin_City","TEXT",3,0,0,40,40,""); 
			echo "State:  ";
			formfield("TLogin_State","TEXT",3,0,0,2,2,""); 
			echo "Zip:  ";
			formfield("TLogin_Zip","TEXT",3,1,1,12,12,""); 
			echo "</TD></TR><TR><TD>";
			formsubmit("Save",3,1,0);
			echo "</TD><TD>";
			formreset("Reset",3,1,1);
			echo "</TD></TR></TABLE><BR>\n<font color=#FF0000 size=+2><B>* - Denotes required field</B></font><BR> ";
			closeform(1);
		}
	}
			
			
	do_footer();			
	dbdisconnect($dbsocket);
%>
