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
	do_header($PageTitle, '1stmaint');
	echo "<B>Maintenance Options</B><BR><BR>\n";
	echo "<TABLE COLS=2 BORDER=1><TR><TD colspan=2><B>DB Table Analyzing</B></TD></TR><TR><TD width=3% align=center>";
	
	openform("maintenance.php","post",2,1,0);
	formsubmit("Analyze TSyslog Table",3,0,0);
	echo "</TD><TD>Analyze TSyslog to re-optimize index.</TD></TR><TR><TD align=center>"; 
	formsubmit("Analyze Syslog_TArchive Table",3,0,0);
	echo "</TD><TD>Analyze Syslog_TArchive to re-optimize index.</TD></TR><TR><TD colspan=2><B>DB Table Vacuuming</B></TD></TR><TR><TD align=center>"; 
	formsubmit("Vacuum Entire Database",3,0,0);
	echo "</TD><TD>Vacuum entire database to re-optimize index and re-use deleted record space</TD></TR><TR><TD align=center>"; 
	formsubmit("FULL Vacuum Entire Database",3,0,0);
	echo "</TD><TD><font color=#FF0000>This is a last resort vacuum that releases unused disk space.  This can take hours!</FONT></TD></TR>";

	echo "<TR><TD colspan=2><B>Basic Table Stats</B></TD></TR><TR><TD align=center>"; 
	formsubmit("View Archive Log Breakdown",3,0,0);
	echo "</TD><TD>Display hosts and their relavent log counts that are archived in the database. <B><font color=#FF0000>RUN WITH CARE!</FONT></B></TD></TR><TR><TD align=center>"; 
	formsubmit("View Unprocessed Log Breakdown",3,0,0);
	echo "</TD><TD>Display hosts and their relavent log counts that are waiting to be processed</TD></TR>";

	echo "<TR><TD colspan=2><B>Reindexing Tables</B></TD></TR><TR><TD align=center>"; 
	formsubmit("Reindex TSyslog",3,0,0);
	echo "</TD><TD>Reindex the TSyslog table</TD></TR><TR><TD align=center>"; 
	formsubmit("Reindex Syslog_TArchive",3,0,0);
	echo "</TD><TD>Reindex the Syslog_TArchive table</TD></TR><TR><TD align=center>"; 
	formsubmit("Reindex SMT Instance",3,0,0);
	echo "</TD><TD>Reindex the entire SMT database instance</TD></TR>"; 
	closeform();

	openform("maintenance.php","post",2,1,0);
	formfield("skip","hidden",3,1,0,200,200,1);
	echo "<TR><TD colspan=2><B>Basic Table Disk Usage</B></TD></TR><TR><TD align=center>"; 
	formsubmit("Display Index Usage",3,0,0);
	echo "</TD><TD>Show how much disk space indexes are taking up</TD></TR><TR><TD align=center>"; 
	formsubmit("Display SMT Table Usage",3,0,0);
	echo "</TD><TD>Show how much disk space SMT Tables are taking up</TD></TR><TR><TD align=center>"; 
	formsubmit("Display Relavent Table Usage",3,0,0);
	echo "</TD><TD>Show how much disk space the Postgresql SMT Instance is taking up</TD></TR>";
	closeform();

	openform("maintenance.php","post",2,1,0);
	echo "<TR><TD colspan=2><B>Configuration Performance Management</B></TD></TR><TR><TD align=center>";
	formfield("skip","hidden",3,1,0,200,200,1);
	formsubmit("Display Current Locks",3,0,0);
	echo "</TD><TD>Provide detailed view of current locks on database.</TD></TR><TR><TD align=center>"; 
	formsubmit("Display Database Confguration",3,0,0);
	echo "</TD><TD>View all of the configuration settings for the database.</TD></TR>"; 

	echo "</TABLE>";
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
