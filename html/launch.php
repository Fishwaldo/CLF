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

	$actiontext="";
	if ( ( $subaction == 1 ) && ( $action == "Save" ) && ( pgdatatrim($shortdesc) != "" ) && ( pgdatatrim($program) != "" )  )  {
                addlaunch($dbsocket,$shortdesc,$longdesc,$program);
		$actiontext="<font color=#FF0000>New record saved</FONT><BR>\n";
		$launchid=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TLaunch","TLaunch_ID","TLaunch_LongDesc='$longdesc'")));
		$action = "Modify";
	}
	if ( ( $subaction == 2 ) && ( $action == "Save" ) && ( idexist($dbsocket,"Syslog_TLaunch","TLaunch_ID",$launchid) ) && 
		( pgdatatrim($shortdesc) != "" ) )  {
                updatelaunch($dbsocket,$launchid,$shortdesc,$longdesc,$program);
		$actiontext="<font color=#FF0000>Record updated</FONT><BR>\n";
	}
	if ( ( $DeleteID == 1 ) && ( $subaction == 3 ) && ( $action == "Delete" ) && 
		( idexist($dbsocket,"Syslog_TLaunch","TLaunch_ID",$launchid) ) )  {
		if ( droplaunch($dbsocket,$launchid) ) {
			$actiontext="<font color=#FF0000>Record deleted</FONT><BR>\n";
		} else {
			$actiontext="<font color=#FF0000>Delete FAILED!</FONT><BR>\n";
		}
		$action="Deleted";
	}
	if ( $action == "Add" ) { 
		$subaction = 1; 
		$launchid = ""; 
	} 
	if ( $action == "Modify" ) { 
		if ( idexist($dbsocket,"Syslog_TLaunch","TLaunch_ID",$launchid) ) {
			$subaction = 2; 
			$shortdesc=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TLaunch","TLaunch_ShortDesc","TLaunch_ID=$launchid")));
			$longdesc=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TLaunch","TLaunch_LongDesc","TLaunch_ID=$launchid")));
			$program=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TLaunch","TLaunch_Program","TLaunch_ID=$launchid")));
		} else {
			dbdisconnect($sec_dbsocket);
			dbdisconnect($dbsocket);
			exit;
		}
	} 
	if ( $action == "Delete" ) { 
		if (  idexist($dbsocket,"Syslog_TLaunch","TLaunch_ID",$launchid) ) {
			$subaction = 3;  
			$shortdesc=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TLaunch","TLaunch_ShortDesc","TLaunch_ID=$launchid")));
		} else {
			dbdisconnect($sec_dbsocket);
			dbdisconnect($dbsocket);
			exit;
		}
	}
	do_header($PageTitle, 'launch');
	openform("launch.php","post",2,1,0);
	echo "<B>Equipment Type</B><BR>\n";
	if ( $subaction != 3 ) {
		echo "1.  Enter Short Description(ie. HP Service Desk):  ";
		formfield("shortdesc","text",3,1,1,30,30,$shortdesc);
		echo "1.  Enter Long Description:  ";
		formfield("longdesc","text",3,1,1,40,250,$longdesc);
		echo "1.  Enter Program w/ Arguments:  ";
		formfield("program","text",3,1,1,40,128,$program);
		formsubmit("Save",3,1,0);
		formfield("subaction","hidden",3,1,0,200,200,$subaction);
		if ( $launchid != "" ) { formfield("launchid","hidden",3,1,0,200,200,$launchid); }
		closeform();
	} else {
		if ( ( $subaction == 3 ) && ( $action == "Delete" ) ) {
			openform("launch.php","post",2,1,0);
			formfield("launchid","Hidden",3,1,0,200,200,$launchid);
			formfield("subaction","Hidden",3,1,0,10,10,$subaction);
			echo "<font color=#FF0000 size=+2><B>Are you sure you want to delete $shortdesc?  ";
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
