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

	if ( $action == "Delete" ) { 
		$hosttype=2; 
	}
	if ( $action == "Add" ) {
		$hostid="";
		unset($host);
	}

	if ( $action== "Save" ) {
		if ( idexist($dbsocket,"Syslog_THost","THost_ID",$hostid) ) {
			if ( ! assignedtoprocessor($dbsocket,$hostid) ) { addprocessorprofile($dbsocket,$userid,$hostid); }
		}
	}
	if ( $action== "Delete" ) {
		if ( idexist($dbsocket,"Syslog_TProcessorProfile","TProcessorProfile_ID",$id) ) {
			dropprocessorprofile($dbsocket,$id);
		}
	}
	
	if ( $action== "Toggle Suspension" ) {
		if ( idexist($dbsocket,"syslog_tsuspend","tlogin_id",$userid) ) {
			deletesuspend($dbsocket,$userid);
		} else {
			addsuspend($dbsocket,$userid);
		}
	}

	$PageTitle="Syslog Management Tool";
	do_header($PageTitle, 'processor');
	echo "<B>Processor Account:  " . sec_username($sec_dbsocket,$userid) . "</B><BR>\n";
	echo "<BR>Status:  ";
	if ( idexist($dbsocket,"Syslog_TSuspend","TLogin_ID",$userid) ) { 
		echo "<font color=#FF0000><B>SUSPENDED</B></FONT><BR><BR>\n";
	} else {
		echo "Not Suspended<BR><BR>\n";
	}

	if ( $action == "Clear Stale Processor" ) {

		if ( ($testmailid = ismailopen($dbsocket,$userid) ) && ( idexist($sec_dbsocket,"Secframe_TLogin","TLogin_ID",$userid) ) ) {
			if ( ! $subaction ) {
				openform("processor.php","post",2,1,0);
				formfield("userid","Hidden",3,1,0,200,200,$userid);
				formfield("subaction","Hidden",3,1,0,10,10,1);
				echo "<font color=#FF0000 size=+2><B>Are you sure you want to clear stale processor : " . sec_username($sec_dbsocket,$userid) . "?  ";
				php?>
				<input type=radio name=Sure value=1>Yes
				<input type=radio name=Sure value=0 checked>No</font><b><BR>
				<?php
				formsubmit("Clear Stale Processor",3,1,0);
				closeform(1);
			} else {
				if ( $Sure) {
					$maildate=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TMail","TMail_Date","TMail_ID=$testmailid")));
					$mailtime=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_TMail","TMail_Time","TMail_ID=$testmailid")));
					$SQLQuery="select distinct TProcess_ID,Syslog_TProcess.THost_ID from Syslog_TProcess,Syslog_TProcessorProfile where ( ( Syslog_TProcessorProfile.TLogin_ID=$userid ) and ( Syslog_TProcessorProfile.THost_ID=Syslog_TProcessorProfile.THost_ID) )";
					$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
						die(pg_errormessage()."\n");
					$SQLNumRows = pg_numrows($SQLQueryResults);
					$PurgeQuery="Begin ; ";
					if ( $SQLNumRows ) {
						for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
							$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
								die(pg_errormessage()."\n");
echo "host: ".$SQLQueryResultsObject->thost_id."<br>";
							$cleanid=stripslashes(pgdatatrim($SQLQueryResultsObject->tprocess_id));
							$cleanhost=gethost($dbsocket,stripslashes(pgdatatrim($SQLQueryResultsObject->thost_id)));
							$PurgeQuery = $PurgeQuery . "delete from Syslog_TAlert where Syslog_TAlert.TSyslog_ID=TSyslog.TSyslog_ID and TSyslog.TSyslog_ID > $cleanid and TSyslog.host='$cleanhost' ; ";
							$PurgeQuery = $PurgeQuery . "delete from Syslog_TArchive where TSyslog_ID > $cleanid and host='$cleanhost' ; ";
						}
						$PurgeQuery = $PurgeQuery . "commit ; ";
						$PurgeSQLQueryResults = pg_exec($dbsocket,$PurgeQuery) or
                                                	die(pg_errormessage()."\n");
					}
					pg_freeresult($SQLQueryResults) or
						die(pg_errormessage() . "\n");
					cleanemail($dbsocket,$testmailid);
					clearlaunchqueue($dbsocket,$testmailid);
					closeopenmail($dbsocket,$testmailid);
				}
				if ( $PurgeSQLQueryResults ) {
					echo "<BR><B>SUCCESS!!</B><BR>\n";
					pg_freeresult($PurgeSQLQueryResults) or
						die(pg_errormessage() . "\n");
				} else {
					echo "<BR><B><font color=#FF0000 size=+2>FAIlED!!</font></B><BR>\n";
					pg_freeresult($PurgeSQLQueryResults) or
						die(pg_errormessage() . "\n");
				}
			}
		} else {
			echo "<BR><B>The processor you've selected is not stale!</B><BR><BR>\n";
		}
	} else {
		openform("processor.php","post",2,1,0);
		formfield("userid","Hidden",3,1,0,200,200,$userid);
		formsubmit("Toggle Suspension",3,1,1);
		closeform();
		echo "<TABLE border=2>";
		echo "<TR><TD><B>Action</B></TD><TD><B>Host</B></TD></TR>\n<TR><TD ALIGN=CENTER VALIGN=CENTER>";
		openform("processor.php","post",2,1,0);
		formsubmit("Save",3,1,0);
		echo "</TD><TD ALIGN=CENTER VALIGN=CENTER>";
	
		hostdropdown ($dbsocket, $sec_dbsocket,"hostid",$REMOTE_ID,$group,0,0,0,1,"",1);
		echo "</TD></TR>\n";
		formfield("userid","Hidden",3,0,0,10,10,$userid);
		closeform();
	
		/* $SQLQuery="select * from Syslog_TProcessorProfile where TLogin_ID=$userid"; */
		$SQLQuery="select TProcessorProfile_ID,Syslog_THost.THost_Host from Syslog_TProcessorProfile where Syslog_TProcessorProfile.TLogin_ID=$userid and Syslog_TProcessorProfile.THost_ID=Syslog_THost.THost_ID order by Syslog_THost.THost_Host";
		$SQLQueryResults=pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows ) {
			echo "<TR><TD><B>Action</B></TD><TD><B>Host</B></TD></TR>\n";
			for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tprocessorprofile_id));
				$host=pgdatatrim($SQLQueryResultsObject->thost_host);
				echo "<TR><TD ALIGN=CENTER VALIGN=CENTER>";
				openform("processor.php","post",2,1,0);
				echo '<input type="submit" name=action value="Delete">';
				echo "</TD><TD VALIGN=CENTER>$host</TD></TR>";
				formfield("userid","Hidden",3,1,0,10,10,$userid);
				formfield("id","Hidden",3,1,0,10,10,$id);
				formfield("host","Hidden",3,1,0,10,128,$host);
				closeform();
			}
		}
		echo "</TABLE>";
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
