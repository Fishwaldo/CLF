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

	$HeaderText="<font size=+1><B>Reports</B></font><BR><BR>\n";
	$FooterText="<font face='Arial, Helvetica, sans-serif' size='-2'><BR>Version " . SMTVER . "<BR>&copy; Jeremy M. Guthrie All rights reserved.</font>\n";
	$PageTitle="Syslog Management Tool";

	/*  set what report options are available */
	$hostselect=0;	/*  allow selecting the host  */
	$dateselect=0;  /*  allow selecting the date  */
	$timeselect=0;  /*  allow selecting the time  */
	$stopdateselect=0;    /*  allow selecting the stop date  */ 
	$stoptimeselect=0;    /*  allow selecting the stop time  */ 
	$timeintervalselect=0;  /*  allow selecting the time interval */
	$severityselect=0;
	$facilityselect=0;
	$stopseverityselect=0;
	$stopfacilityselect=0;
	$steps=0;	/*  reset the number of steps in a process */

	if ( ! isset($reporttype) ) { $reporttype == 1 ; }
	if ( $reporttype <= 4 ) {
		$hostselect=1;
		$dateselect=1;
		$timeselect=1;
		$stopdateselect=1;
		$stoptimeselect=1;
		$timeintervalselect=1;
	}

php?>
<HTML>
	<HEAD>
		<TITLE>
<?php	echo $PageTitle; php?>
		</TITLE>
	</HEAD>
<?php
	startbody();
	echo $HeaderText;
	switch ($reporttype) {
		case 3:
			openform("reports/cisco-pix-bandwidthbreakdown.php","post",2,1,0);
			break;
		case 4:
			openform("reports/vpnuserusage.php","post",2,1,0);
			break;
		default:
			openform("background.php","post",2,1,0);
			break;
	}

	echo "<form size=+1><B>Report Type:  " . reporttypename($reporttype) . "</B></FONT><BR><BR>\n";
	formfield("reporttype","hidden",3,1,0,200,200,$reporttype);
	if ( $hostselect ) {
		$steps++;
		echo "<B>Step #$steps:  </B><BR>\n";
		echo "<TABLE COLS=2 BORDER=1><TR><TD width=20><input type=radio name=datatype value=1 checked></TD><TD>Host:  ";
		hostdropdown ($dbsocket, $sec_dbsocket, "hostid", $REMOTE_ID,$group);
		crbr(1,0);
		echo "</TD></TR>";
		if ( $group >= 2 ) {
			echo "<TR><TD width=20><input type=radio name=datatype value=4></TD><TD>By User and By Host Type</TD></TR>\n";
		}
		echo "<TR><TD width=20><input type=radio name=datatype value=2></TD><TD>Host Type:  ";
		premadetypedropdown ($dbsocket, "typeid",0,0,1,1,$typeid);
		echo "</TD><TR>";
		if ( $group >= 2 ) {
			echo "</TD><TD width=20><input type=radio name=datatype value=3></TD><TD>User:  ";
			userdropdownbox ($sec_dbsocket,"userid",2,1,0,1,"",$groupid);
			echo "</TD><TR>\n";
		}
		echo "</TABLE>\n";
	}
	if ( $dateselect ) {
		$steps++;
		$month=date("M",time());
		$day=date("d",time());
		$year=date("Y",time());
		echo "<B>Step #$steps:</B>  Date:  ";
		monthdropdown ("month",0,0,0,1,$month);
		daydropdown("day",0,0,0,1,$day);
		yeardropdown("year",0,1,1,1,$year);
	}
	if ( $timeselect ) {
		$steps++;
		$hour=date("G",time());
		$minute=date("i",time());
		echo "<B>Step #$steps:</B>  Time:  ";
		hourdropdown("hour",0,0,0,1,$hour);
		echo ":";
		minutedropdown("minute",0,1,1,1,$minute);
	}
	if ( $stopdateselect ) {
		$steps++;
		$month2=date("M",time());
		$day2=date("d",time());
		$year2=date("Y",time());
		echo "<B>Step #$steps:</B>  Stop Date:  ";
		monthdropdown ("month2",0,0,0,1,$month2);
		daydropdown("day2",0,0,0,1,$day2);
		yeardropdown("year2",0,1,1,1,$year2);
	}
	if ( $stoptimeselect ) {
		$steps++;
		$hour2=date("G",time());
		$minute2=date("i",time());
		echo "<B>Step #$steps:</B>  Stop Time:  ";
		hourdropdown("hour2",0,0,0,1,$hour2);
		echo ":";
		minutedropdown("minute2",0,1,1,1,$minute2);
	}
	if ( $severityselect ) {
		$steps++;
		echo "<B>Step #$steps:</B>  Severity:  ";
		severitydropdown("facility",1,1,1,1,0);
	}
	if ( $facilityselect ) {
		$steps++;
		echo "<B>Step #$steps:</B>  Facility:  ";
		facilitydropdown("facility",1,1,1,1,0);
	}
		
        formsubmit("View Report",3,1,0);
	closeform();
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	echo $FooterText;
php?>
	</BODY>
</HTML>
<?php
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
