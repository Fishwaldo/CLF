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

	if ( $group < 1 ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}

	$PageTitle="Edit Filters";

	do_header($PageTitle, '1stfilter');
	echo "<TABLE width=100?><TR><TD>";
	openform("filter.php","post",2,1,0);
	echo "<B>Filter Entries</B><BR>\n"; 
	echo "1.  Choose Filter:  ";
	filterdropdown ($dbsocket,"filterid",$REMOTE_ID,3,1,1,1,"",1);
        formsubmit("Add",3,1,0);
        formsubmit("Modify",3,1,0);
        formsubmit("Delete",3,1,0);
	formfield("filtermain","Hidden",3,1,0,10,10,1);
        closeform();
	if ( $group >= 3 ) {
		echo "</TD><TD>";
		openform("filter.php","post",2,1,0);
		echo "<B>Delete User Filters</B><BR>\n";
		echo "1.  Select User:  ";
		userdropdownbox ($sec_dbsocket,"userid",2,1,1,1);
		formfield("filtermain","Hidden",3,1,0,10,10,1);
        	formsubmit("Delete User Filters",3,1,0);
       		closeform();
	}
	echo "</TD></TR></TABLE>\n";
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";

	do_footer();	
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
