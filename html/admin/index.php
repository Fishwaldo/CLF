<?php
/*=============================================================================
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


	require_once('../config.php');

	$dbsocket=sec_dbconnect();

	$REMOTE_ID=sec_usernametoid($dbsocket,$_SERVER['REMOTE_USER']);
	$ADMIN_ID=sec_groupnametoid($dbsocket,'Administrators');

	if ( ! sec_groupmember($dbsocket,$REMOTE_ID,$ADMIN_ID) ) { 
		dbdisconnect($dbsocket);
		exit; 
	} 
	
	$PageTitle="Security Framework Administration";
	do_header($PageTitle, 'adminindex');
?>
	<table width=100% border=0 valign=top>
		<tr><td width=50?>
		<B><font size=+1>Group Administration</B></font><BR>
<?php
		openform("group.php","post",2,1,0);
		echo "Select group:  ";
		groupdropdownbox ($dbsocket,"TGroup_ID",3,1,1,1,"");
                formsubmit("Add",3,1,0);
                formsubmit("Modify",3,1,0);
                formsubmit("Delete",3,1,0);
                formsubmit("Adjust Membership",3,1,0);
		closeform(1);
?>		
		</td><td width=50?>
		<B><font size=+1>Application Administration</font></B><BR>
<?php
		openform("app.php","post",2,1,0);
		echo "Select Application:  ";
		appdropdownbox ($dbsocket,"TApp_ID",3,1,1,1,"");
                formsubmit("Add",3,1,0);
                formsubmit("Modify",3,1,0);
                formsubmit("Delete",3,1,0);
                formsubmit("Adjust ACL",3,1,0);
		closeform(1);
?>
		</td></tr>
		<tr><td width=50?>
		<BR><B><font size=+1>User Administration</font></B><BR>
<?php     
                openform("user.php","post",2,1,0);
                echo "Select User:  ";
		userdropdownbox ($dbsocket,"TLogin_ID",3,1,1,1,"");
                formsubmit("Add",3,1,0);
                formsubmit("Modify",3,1,0);
                formsubmit("Delete",3,1,0);
                closeform(1);
?>                          
		</td><td width=50?>
                </td></tr>
	</table>
<?php
	do_footer();
	dbdisconnect($dbsocket);
?>
