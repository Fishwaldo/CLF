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

	require_once('config.php');

	$sec_dbsocket=sec_dbconnect();
	$REMOTE_ID=sec_usernametoid($sec_dbsocket,$_SERVER['REMOTE_USER']);
	$APP_ID=sec_appnametoid($sec_dbsocket,'SyslogOp');
	if ( ! sec_accessallowed($sec_dbsocket,$REMOTE_ID,$APP_ID) ) {
		dbdisconnect($sec_dbsocket);
		exit;
	}
	$group=0;
	$mrtg=0;
	$acid=0;
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Customer'); 
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=1; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Analyst');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=2; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Administrators');
	if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=3; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Administrators');
	if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=4; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'MRTG');
	if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $mrtg=1; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'ACID');
	if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $acid=1; }
	$dbsocket=dbconnect(SMACDB,"msyslog",SMACPASS);
        if ( $group == 0 ) {
                dbdisconnect($sec_dbsocket);
                dbdisconnect($dbsocket);
                exit;
        }

	$HeaderText="<a href=http://www.csc.com/ target='main'><img width = 120 src=images/title.png></a><BR><BR>";
	$FooterText="<TR><TD WIDTH=" . LEFTWIDTH . "><font face='Arial, Helvetica, sans-serif' size='-2'><BR>Version " . SMTVER . "<BR>&copy; Copyright 2004 Computer Sciences Corporation</font></TD></TR>\n";
	$PageTitle="Centralized Logging Server";
?>
<HTML>
	<HEAD>
		<TITLE>
<?php	echo $PageTitle; ?>
		</TITLE>
	<LINK REL="Stylesheet" HREF="include_main.css" type="text/css">  
	</HEAD>
	<BODY background='images/background.gif' topMargin=0  MARGINWIDTH="0" MARGINHEIGHT="0" leftMargin=0>
<TABLE cellSpacing=0 cellPadding=0 width=100% border=0>
  <TBODY>
  <TR>
    <TD vAlign=top width=190>
      <TABLE cellSpacing=0 cellPadding=0 width=100% border=0>
        <TBODY>
        <TR>
          <TD><A href="http://www.csc.com/" target=_new><IMG height=64 alt="CSC Home Page"
            src="images/Px_Clear.gif" width=190 border=0></A></TD></TR>
        <TR>
          <TD>
            <TABLE id=countrySel cellSpacing=0 cellPadding=0 border=0 width=100%>
              <TBODY>
              <TR>
                <TD bgColor=#cccccc><IMG height=6 src="images/over_nav_qing.gif" width=190 border=0></TD></TR></TBODY></TABLE></TD></TR>
			  </TBODY></TABLE></TD>
			  <!-- Vertical White line between nav and rest of home page -->
			  <TD width=1 vAlign=bottom bgColor=#cc0000><IMG alt="" src="images/blue.gif" width=1
border=0></td>
			  </tr><tr height=1000><td vAlign=top>
<?php
//	echo $HeaderText;
	echo "<table>";
	echo "<TR><TD WIDTH=" . LEFTWIDTH . "><b>Log Options</b><BR>";
	if ( $group >= 1 ) {
		echo tabs(2) . "<tr><td WIDTH=" . LEFTWIDTH . "><LI><a href=1stview.php target='main'>Syslogs</a></TD></TR>";
		echo tabs(2) . "<tr><TD WIDTH=" . LEFTWIDTH . "><LI><A HREF=1stfilter.php target='main'>Filters</A></TD></TR>";
		echo tabs(2) . "<tr><TD WIDTH=" . LEFTWIDTH . "><LI><A HREF=logwatch.php target='main'>Reports</A></TD></TR>";
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1stalertview.php target='main'>Alerts</a></TD></TR>";
		if ( userhasruleaccess ($dbsocket,$REMOTE_ID) ) {
			echo "<TR><TD><BR><b>Administration</B></TD></TR>";
			echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1strule.php target='main'>Rules</a></TD></TR>\n";
		}
	}
	if ( $group >= 2 ) {
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1stsaves.php target='main'>View Saved Logs</A></TD></TR>";
	}
	echo "</TR>";
	if ( $group >= 3 ) {
		echo "<TR><TD><BR><b>Administration</B></TD></TR>";
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1sthost.php target='main'>Hosts</a></TD></TR>";
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1strule.php target='main'>Rules</a></TD></TR>\n";
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1stcustomer.php target='main'>Customers</a></TD></TR>";
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1stprocessor.php target='main'>Processors</a></TD></TR>\n";
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1stequiptype.php target='main'>Equip. Types</a></TD></TR>\n";
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1stlaunch.php target='main'>Launch Programs</a></TD></TR>\n";
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=1stmaint.php target='main'>System Maint.</a></TD></TR>\n";
	}
	if ( $group >= 4 ) {
		echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=../admin/index.php target='main'>Security Framework</a></TD></TR>\n";
	}
	if ( $mrtg || $acid ) {
		echo "<TR><TD><BR><b>Other Applications</B></TD></TR>";
		if ( $mrtg ) { 
			echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=/cgi-bin/14all.cgi target='main'>MRTG Graphs</a></TD></TR>";
		}
		if ( $acid ) { 
			echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=/login/acid/ target='main'>A.C.I.D.</a></TD></TR>";
		}
	}
	echo tabs(2) . "<TR><TD WIDTH=" . LEFTWIDTH . "><LI><a href=logout.php target=_top>Logout</a></TD></TR>";
	
	echo $FooterText;
	echo "</TABLE>\n";

?>

	</td>
			  <TD width=1 vAlign=bottom bgColor=#cc0000><IMG alt="" src="images/blue.gif" width=1
border=0></td>


</tr></table>
	</BODY>
</HTML>
<?php
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
