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

        if ( $group == 0 ) {
                dbdisconnect($sec_dbsocket);
                dbdisconnect($dbsocket);
                exit;
        }


	do_header("View Alerts", '1stalerts');
	$month=date("M",time());
	$day=date("d",time());
	$year=date("Y",time());

	echo $HeaderText;
	echo "<TABLE width=100%><TR><TD>";

	openform("alert.php","post",2,1,0);
	formfield("viewtype","Hidden",3,1,0,10,10,2);


	echo "<B>View Alerts for Specific Hosts</B><BR>\n";
	echo "1.  Select View Type:<BR>\n  ";
	echo "<TABLE COLS=2 BORDER=1><TR><TD ><input type=radio name=datatype value=1 checked></TD><TD>Host:  ";
	hostdropdown ($dbsocket, $sec_dbsocket, "hostid", $REMOTE_ID,$group);
	crbr(1,0);
	echo "</TD></TR>";
	if ( $group >= 2 ) {
		echo "<TR><TD ><input type=radio name=datatype value=4></TD><TD>By Customer User and By Host Type</TD></TR>\n";
	}
	echo "<TR><TD ><input type=radio name=datatype value=2></TD><TD>Host Type:  ";
	premadetypedropdown ($dbsocket, "typeid",0,0,1,1,$typeid);
	echo "</TD><TR>";
	if ( $group >= 2 ) {
		echo "</TD><TD><input type=radio name=datatype value=3></TD><TD>Customer User:  ";
		$groupid=sec_groupnametoid($sec_dbsocket,'Syslog Customer');
		userdropdownbox ($sec_dbsocket,"userid",2,1,0,1,"",$groupid);
		echo "</TD><TR>\n";
	}
	echo "</TABLE>\n2.  Date:  ";
	monthdropdown ("month",0,0,0,1,$month);
	echo "/";
	daydropdown("day",0,0,0,1,$day);
	echo "/";
	yeardropdown("year",0,0,0,1,$year);
	crbr(1,1);
	echo "3.  Aggregate Results:  <input type=radio name=aggregate value=1>Yes  <input type=radio name=aggregate value=0 checked>No<BR>\n";
	formsubmit("View",3,1,0);
	closeform();

	echo "</TD><TD VALIGN=top>";
	if ( $group >= 2 ) {
		echo "<B>View All Alerts for a Given Day</B><BR>\n";
		openform("alert.php","post",2,1,0);
		formfield("viewtype","Hidden",3,1,0,10,10,1);
		echo "1.  Date:  ";
		monthdropdown ("month",0,0,0,1,$month);
		echo "/";
		daydropdown("day",0,0,0,1,$day);
		echo "/";
		yeardropdown("year",0,1,1,1,$year);
		echo "2.  Aggregate Results:  <input type=radio name=aggregate value=1 checked>Yes  <input type=radio name=aggregate value=0>No<BR>\n";
		formsubmit("View",3,1,0);
		closeform();
	}
	echo "</TR></TD></TABLE>\n";
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
