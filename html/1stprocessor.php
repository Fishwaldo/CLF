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

	if ( $group != 3 ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}

	$PageTitle="Syslog Management Tool";
	do_header($PageTitle, '1stprocessor');
	openform("processor.php","post",2,1,0);
	echo "<B>Modify Processor</B><BR>\n";
	echo "1.  Select Processor Account:  ";	
        $groupid=sec_groupnametoid($sec_dbsocket,'Syslog msyslog');
	userdropdownbox ($sec_dbsocket,"userid",2,1,1,1,"",$groupid);
	formsubmit("Modify",3,1,0);
	formsubmit("Clear Stale Processor",3,1,0);

	echo "<BR>\n";
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
