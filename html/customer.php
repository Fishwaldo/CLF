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
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Administrators');
	if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=3; }
	$dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);

	if ( ( $group != 3 ) || ( $userid == "" ) ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}

	if ( ( $action == "Delete" ) && ( $id > 0 ) ) {
		dropcustomerhost($dbsocket,$id);
	}
	if ( ( $action == "Add" ) && ( count($hostid) >= 1 ) && ( $userid != "" ) ) {
		for ( $loop=0 ; $loop != count($hostid) ; $loop++ ) {
			if ( $hostid != "" ) {
				if ( idexist($dbsocket,"Syslog_THost","THost_ID",$hostid[$loop]) ) {
					if ( ! assignedtouser ($dbsocket,$userid,$hostid[$loop]) ) { addcustomerhost($dbsocket,$hostid[$loop],$userid,$allowedit); }
				}
			}
		}
	}
	if ( ( $action == "Save" ) && ( $assignedhostid != "" ) ) {
		if ( assignedtouser ($dbsocket,$userid,$assignedhostid) ) { 
			dropcustomerhost($dbsocket,$id);
			addcustomerhost($dbsocket,$assignedhostid,$userid,$existallowedit); 
		}
	}
	if ( ( $action == "Clone") && ( idexist($dbsocket,"Syslog_TCustomerProfile","TLogin_ID",$userid) ) ) { 
		$groupid=sec_groupnametoid($sec_dbsocket,'Syslog Customer');
		if ( ( sec_groupmember($sec_dbsocket,$userid,$groupid) ) &&
		     ( sec_groupmember($sec_dbsocket,$duserid,$groupid) ) ) {
			$SQLQuery="select TCustomerProfile_EditRules,THost_ID from Syslog_TCustomerProfile where Syslog_TCustomerProfile.TLogin_ID=$userid";
			$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
			if ( $SQLNumRows ) {
				for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
					$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
						die(pg_errormessage()."<BR>\n");
					$hostid=pgdatatrim($SQLQueryResultsObject->thost_id);
					$allowedit=$SQLQueryResultsObject->tcustomerprofile_editrules;
					if ( ! assignedtouser ($dbsocket,$duserid,$hostid) ) { addcustomerhost($dbsocket,$hostid,$duserid,$allowedit); }
				}
			}
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		} 
		$userid=$duserid;
	}

	$PageTitle="Syslog Management Tool";
	do_header($PageTitle, 'customer');

	echo "<B>Customer:  " . sec_username($sec_dbsocket,$userid) . "</B><BR>\n";
	$SQLQuery="select THost_ID,TCustomerProfile_EditRules,TCustomerProfile_ID,Syslog_THost.THost_Host from Syslog_TCustomerProfile where Syslog_TCustomerProfile.TLogin_ID=$userid and Syslog_TCustomerProfile.THost_ID=Syslog_THost.THost_ID order by Syslog_THost.THost_Host";
	$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	openform("customer.php","post",2,1,0);
	formfield("host","Hidden",3,1,0,10,10,$host);
	formfield("userid","Hidden",3,1,0,10,10,$userid);
	echo "<TABLE BORDER=2>\n";
	echo "<TR><TD><B>Action</B></TD><TD><B>Host</B></TD><TD><B>Allow Host Rule Edits</B></TD></TR>";
	echo "<TR><TD><input type=submit name=action value='Add'></TD><TD>" ; 
	hostdropdown ($dbsocket, $sec_dbsocket, "hostid[]", $REMOTE_ID,$group,0,0,0,5);
	echo "</TD><TD align=center><input type=checkbox name=allowedit value=1></TD></TR>\n";
	closeform();
	if ( $SQLNumRows ) {
		echo "<TR><TD><B>Action</B></TD><TD><B>Host</B></TD></TR>";
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tcustomerprofile_id));
			$host=pgdatatrim($SQLQueryResultsObject->thost_host);
			$assignedhostid=pgdatatrim($SQLQueryResultsObject->thost_id);
			$allowedit=$SQLQueryResultsObject->tcustomerprofile_editrules;
			openform("customer.php","post",2,1,0);
			formfield("userid","Hidden",3,1,0,10,10,$userid);
			formfield("id","Hidden",3,1,0,10,10,$id);
			echo "<TR><TD>";
			echo '<input type="submit" name=action value="Delete">';
			echo '<input type="submit" name=action value="Save"></TD>';
			echo "<TD>$host</TD><TD align=center>";
			formfield("assignedhostid","Hidden",3,1,0,10,10,$assignedhostid);
			if ( $allowedit ) {
				echo "<input type=checkbox name=existallowedit value=1 checked>";
			} else {
				echo "<input type=checkbox name=existallowedit value=1>";
			} 
			echo "</TD></TR>";
			closeform();
			echo "</TD></TR>\n";
		}
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	echo "</TABLE>\n";
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
