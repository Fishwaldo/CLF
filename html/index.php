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
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Customer');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=1; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Analyst');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=2; }
        $GROUP_ID=sec_groupnametoid($sec_dbsocket,'Syslog Administrators');
        if ( sec_groupmember($sec_dbsocket,$REMOTE_ID,$GROUP_ID) ) { $group=3; }
?>
<html>

        <head>
                <title>Centralized Logging Framework</title>
        </head>
        <frameset cols="191,*" border="1" framespacing="0" frameborder="no">
                <frame src="menu.php" name="nav" scrolling="no">
                <frameset rows="*" border="0" framespacing="0">
                        <frame src="background.php" name="main" scrolling="yes">
                </frameset>
        </frameset>
	<body background='images/background.gif');
        <noframes>

                        <p></p>
                </body>

        </noframes>

</html>
<?php
        dbdisconnect($sec_dbsocket);
?>
