#!/opt/bin/php
<%
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

	require_once('../../config.php');
        $dbsocket=sec_dbconnect();

        $SQLQuery="select TLogin_ID,TLogin_Username,TLogin_Password from SecFrame_TLogin;";
        $SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
                die(pg_errormessage()."<BR>\n");
        $SQLNumRows = pg_numrows($SQLQueryResults);
        for ( $loop =0 ; $loop != $SQLNumRows ; $loop++ ) {
                $SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
                        die(pg_errormessage()."<BR>\n");
                $md5pass=md5($SQLQueryResultsObject->tlogin_password);
                echo "$SQLQueryResultsObject->tlogin_id:  $SQLQueryResultsObject->tlogin_username:  " . md5($SQLQueryResultsObject->tlogin_password) . "\n";
                $SQLQuery="update SecFrame_TLogin set TLogin_Password='$md5pass' where TLogin_ID=$SQLQueryResultsObject->tlogin_id";
                $NewSQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
                        die(pg_errormessage()."<BR>\n");
                pg_freeresult($NewSQLQueryResults) or
                        die(pg_errormessage()."<BR>\n");
        }




        pg_freeresult($SQLQueryResults) or
                die(pg_errormessage() . "<BR>\n");

%>

