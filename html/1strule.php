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

	$dbsocket = dbconnect(SMACDB,"msyslog",SMACPASS);

	if ( ( $group != 3 ) && ( ! userhasruleaccess ($dbsocket,$REMOTE_ID) ) )  {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}

	$PageTitle="Syslog Management Tool";
	do_header($pageTitle, '1strule');

	if ( $group == 3 ) {
		echo "<TABLE width=100%><TR><TD WIDTH=50%><B>Pre-made rules</B></TD><TD WIDTH=50%><B>Host Rules</B></TD></TR>\n";
		echo "<TR><TD valign=top>";
		openform("rule.php","post",2,1,0);
		echo "1.  Choose Rule:  ";
		pixruledropdown ($dbsocket, "id",2,1,0,1);
		crbr(1,1);
		formfield("ruletype","Hidden",3,1,0,10,10,1);
		formsubmit("Add",3,1,0);
		formsubmit("Modify",3,1,0);
		formsubmit("Delete",3,1,0);
		closeform();
	
		echo "</TD><TD>";
		openform("rule.php","post",2,1,0);
		formfield("ruletype","Hidden",3,1,0,10,10,2);
		echo "1.  Modify Host Rules:  ";
		hostdropdown ($dbsocket, $sec_dbsocket, "hostid", $REMOTE_ID,$group);
		crbr(1,1);
		formsubmit("Modify",3,1,0);
		closeform();
	
		echo "</TD></TR><TR><TD><BR>";
		if ( numberofrecords($dbsocket,"THost_ID","syslog_thost") > 1 ) {
			echo "<B>Clone Rules</B>\n";
			openform("rule.php","post",2,1,0);
			formfield("ruletype","Hidden",3,1,0,10,10,3);
			echo "1.  Clone Source:\n";
			hostdropdown ($dbsocket, $sec_dbsocket, "source", $REMOTE_ID,$group);
			echo "<BR>2.  Clone Destination:\n";
			hostdropdown ($dbsocket, $sec_dbsocket, "destination", $REMOTE_ID,$group);
			crbr(1,1);
			formsubmit("Clone",3,1,0);
		}
		echo "</TD></TR>";
	
		echo "</TD></TR>\n";
		echo "</TABLE>\n";
	} else {
		openform("rule.php","post",2,1,0);
		formfield("ruletype","Hidden",3,1,0,10,10,2);
		echo "1.  Modify Host Rules:  ";
		hostdropdown ($dbsocket, $sec_dbsocket, "hostid", $REMOTE_ID,$group);
		crbr(1,1);
		formsubmit("Modify",3,1,0);
		closeform();
	}	
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
