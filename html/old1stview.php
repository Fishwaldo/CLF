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

	$HeaderText="<font size=+1><B>Syslog Management</B></font><BR><BR>";
	$FooterText="<font face='Arial, Helvetica, sans-serif' size='-2'><BR>Version " . SMTVER . "<BR>&copy; Jeremy M. Guthrie All rights reserved.</font>\n";
	$PageTitle="Syslog Management Tool";
?>
<HTML>
	<HEAD>
		<TITLE>
<?php	echo $PageTitle; ?>
		</TITLE>
	</HEAD>
<?php	
	startbody();
	echo $HeaderText;
	$month=date("M",time());
	$day=date("d",time());
	$year=date("Y",time());
	$hour=date("G",time());
	$minute=date("i",time());

	if ( $group >= 1 ) {
		openform("view.php","post",2,1,0);
		echo "<B>View Specific Time Frame</B><BR><BR>\n";
		echo "1.  Select View Type:  ";
		echo "<TABLE COLS=2 BORDER=1><TR><TD><input type=radio name=datatype value=1 checked></TD><TD>Host:  ";
		hostdropdown ($dbsocket, $sec_dbsocket, "hostid", $REMOTE_ID,$group);
		crbr(1,0);
		echo "</TD></TR>";
		if ( $group >= 2 ) {
			echo "<TR><TD width=20><input type=radio name=datatype value=4></TD><TD>By Group and By Host Type (Select Below)</TD></TR>\n";
		}
		echo "<TR><TD width=20><input type=radio name=datatype value=2></TD><TD>Host Type:  ";
		if (! isset($typeid)) {
			$typeid = '';
		}
		premadetypedropdown ($dbsocket, "typeid",0,0,1,1,$typeid);
		echo "</TD><TR>";
		if ( $group >= 2 ) {
			echo "</TD><TD width=20><input type=radio name=datatype value=3></TD><TD>Group:  ";
			$groupid=sec_groupnametoid($sec_dbsocket,'Syslog Customer');
			userdropdownbox ($sec_dbsocket,"userid",2,1,0,1,"",$groupid);
			echo "</TD></TR>\n";
		}	
		echo "</table>2. Select Time Range:<br><table border=1 width=100%><TR><TD>";
		echo "Start Date:</TD><TD>";
		monthdropdown ("month",0,0,0,1,$month);
		echo "/";
		daydropdown("day",0,0,0,1,$day);
		echo "/";
		yeardropdown("year",0,0,0,1,$year);
		echo " Time:  ";
		hourdropdown("hour", 0, 0, 0, 1, $hour);
		echo ":";
		minutedropdown("minute", 0, 1, 1, $lines=1, $minute);
		echo "</TD></TR><tr><td><input type=radio name=durtype value=1 checked>Duration:</td><td>";
		durationdropdown("duration");
		echo "</td></tr><tr><td><input type=radio name=durtype value=2>";
		echo "End Date:</TD><TD>";
		monthdropdown ("emonth",0,0,0,1,$month);
		echo "/";
		daydropdown("eday",0,0,0,1,$day);
		echo "/";
		yeardropdown("eyear",0,0,0,1,$year);
		echo " Time:  ";
		hourdropdown("ehour", 0, 0, 0, 1, $hour);
		echo ":";
		minutedropdown("eminute", 0, 1, 1, $lines=1, $minute);
		echo "</TD></TR>";
		echo "<TR><TD><input type=radio name=durtype value=3>View Data From Last  Minutes:</TD><TD><input type='text' name='lastfive' value = '' size='4'></TD></TR></table>";

		echo "<table border = 1><TR><TD>Page Breaks:</TD><TD>Yes<input type='radio' name='pagebreak' value='1' checked>";
		echo "  No<input type='radio' name='pagebreak' value='0'></TD></TR>";
		echo "<TR><TD>Lines/Page:</TD><TD>";
		pagesize("pagesize",2,1);
		echo "</TD></TR></TABLE>";	

		formfield("viewtype","Hidden",3,1,0,10,10,2);
		echo "Choose Filter Type(Optional)<BR><TABLE BORDER=1><TR><TD>";
	        echo "<input type=radio name=regexpinclude[] value=0 checked>Exclude  ";
	        echo "<input type=radio name=regexpinclude[] value=1>Include<BR>\n";
		echo "Regular Expression Filter:  ";
		formfield("regexp[]","text",3,1,1,20,40);
		echo "</TD></TR><TR><TD>\n";
		echo "<input type='checkbox' name='filter' value='1'>Use Premade Filter:  ";
		filterdropdown ($dbsocket,"filterid",$REMOTE_ID);
		echo "</TR><TR><TD>Filter Type:  <input type=radio name=filterorlevel[] value=1 checked>Expression  ";
		echo "<input type=radio name=filterorlevel[] value=3>Facility & Severity  ";
		echo "<input type=radio name=filterorlevel[] value=2>Expression w/ Facility & Severity";
		echo "</TD></TR><TR><TD>";
		echo "Facility Range:  ";
		facilitydropdown("startfacility[]",1,0,0,1,0);
		echo " to ";
		facilitydropdown("stopfacility[]",1,0,0,1,23);
		echo "</TD></TR><TR><TD>Severity Range:  ";
		severitydropdown("startseverity[]",1,0,0,1,0);
		echo " to ";
		severitydropdown("stopseverity[]",1,0,0,1,7);

		echo "</TD></TR></TABLE>\n";
		formsubmit("View",3,1,1);
		closeform();
		crbr(1,1);
	}
	$endtime=time();
	echo "Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	echo $FooterText;
?>
	</BODY>
</HTML>
<?php
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
