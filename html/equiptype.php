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
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=1; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Analyst');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=2; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Administrators');
	if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=3; }
	$dbsocket= dbconnect(SMACDB,"msyslog",SMACPASS);

	if ( $group != 3 ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}

	$PageTitle="Syslog Management Tool";
	do_header($PageTitle, 'equiptype');
	$actiontext="";
	if ( ( $subaction == 1 ) && ( $action == "Save" ) && ( pgdatatrim($typedesc) != "" ) )  {
                addequiptype($dbsocket,$typedesc, $logwatch);
		$actiontext="<font color=#FF0000>New record saved</FONT><BR>\n";
		$typeid=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TPremadeType","TPremadeType_ID","TPremadeType_Desc='$typedesc'")));
		$action = "Modify";
	}
	if ( ( $subaction == 2 ) && ( $action == "Save" ) && ( idexist($dbsocket,"Syslog_TPremadeType","TPremadeType_ID",$typeid) ) && 
		( pgdatatrim($typedesc) != "" ) )  {
                updateequiptype($dbsocket,$typeid,$typedesc, $logwatch);
		$actiontext="<font color=#FF0000>Record updated</FONT><BR>\n";
	}
	if ( ( $DeleteID == 1 ) && ( $subaction == 3 ) && ( $action == "Delete" ) && 
		( idexist($dbsocket,"Syslog_TPremadeType","TPremadeType_ID",$typeid) ) )  {
		if ( numberofhostsusingtype($dbsocket,$typeid) < 1 ) {
			dropequiptype($dbsocket,$typeid);
			$actiontext="<font color=#FF0000>Record deleted</FONT><BR>\n";
		} else {
			$actiontext="<font color=#FF0000>Cannot delete record because hosts already reference premade type</FONT><BR>\n";
		}
		$action="Deleted";
	}
	if ( $action == "Add" ) { 
		$subaction = 1; 
		$typeid = ""; 
	} 
	if ( $action == "Modify" ) { 
		$subaction = 2; 
		$typedesc=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TPremadeType","TPremadeType_Desc","TPremadeType_ID=$typeid")));
 		$logwatch=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TPremadeType","logwatch_cmd","TPremadeType_ID=$typeid")));

	} 
	if ( $action == "Delete" ) { 
		$subaction = 3;  
		$typedesc=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TPremadeType","TPremadeType_Desc","TPremadeType_ID=$typeid")));
 		$logwatch=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TPremadeType","logwatch_cmd","TPremadeType_ID=$typeid")));
	}
	openform("equiptype.php","post",2,1,0);
	echo "<B>Equipment Type</B><BR>\n";
	if ( $subaction != 3 ) {

		echo "1.  Enter Equipment Type:  ";
		formfield("typedesc","text",3,1,1,40,40,$typedesc);
		echo "2.  Enter Logwatch Command Line:  ";
		echo "<select name=\"logwatch\"><option>Unselected</option>";
		$handle=opendir($logwatchreports);
		while ($file = readdir($handle)) {
			if ($file == ".." || $file == ".") {
				continue;
			}
			if(is_dir($logwatchreports."/".$file)) {
				echo "<option value=\"$file\"";
				if ($logwatch == $file) {
					echo " selected";
				}
				echo ">$file</option>";
			}
		}
		echo "</select><br>";
		formsubmit("Save",3,1,0);
		formfield("subaction","hidden",3,1,0,200,200,$subaction);
		if ( $typeid != "" ) { formfield("typeid","hidden",3,1,0,200,200,$typeid); }
		closeform();
	} else {
		if ( ( $subaction == 3 ) && ( $action == "Delete" ) ) {
			openform("equiptype.php","post",2,1,0);
			formfield("typeid","Hidden",3,1,0,200,200,$typeid);
			formfield("subaction","Hidden",3,1,0,10,10,$subaction);
			echo "<font color=#FF0000 size=+2><B>Are you sure you want to delete $typedesc?  ";
	php?>
			<input type=radio name=DeleteID value=1>Yes
			<input type=radio name=DeleteID value=0 checked>No</font><b><BR>
	<?php
			formsubmit("Delete",3,1,0);
			closeform(1);
		}		
	}

	echo $actiontext;

	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
