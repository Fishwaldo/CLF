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
	do_header($PageTitle, 'maintenance');
	$actiontext="";
	echo "$action<BR>\n";
	if ( $action == "Reindex SMT Instance" ) { 
		echo "Reindexing all indexes....";
		$SQLQuery="select indexrelname from pg_statio_all_indexes where pg_statio_all_indexes.schemaname='public' order by indexrelname";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows ) {
			for ( $loop=0 ; $loop != $SQLNumRows ; $loop++ ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$starttime=time();
				$SQLQuery="reindex index $SQLQueryResultsObject->indexrelname;";
				$TempSQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
					die(pg_errormessage()."<BR>\n");
				pg_freeresult($TempSQLQueryResults) or
					die(pg_errormessage() . "<BR>\n");
				$endtime=time();
				echo "Reindex of $SQLQueryResultsObject->indexrelname done in " . ($starttime - $begintime) . " seconds.<BR>\n  " ;
			}
		}
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		echo "Finished!<BR>\n";
	}
	if ( $action == "Reindex TSyslog" ) { 
		echo "Reindexing TSyslog....";
		$SQLQuery="reindex index tsyslog_pkey ; reindex index host_Idx ;reindex index TSyslogDateTime_IDX ; reindex index TSyslHostID_Idx ; ";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		echo "Finished!<BR>\n";
	}
	if ( $action == "Reindex Syslog_TArchive" ) { 
		echo "Reindexing Syslog_TArchive....";
		$SQLQuery="reindex index syslog_tarchive_pkey ; reindex index archhost_idx ; reindex index tarchdatetime_idx ;";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		echo "Finished!<BR>\n";
	}
	if ( $action == "Vacuum Entire Database" ) { 
		echo "Conducting Vacuum....";
		$SQLQuery="vacuum ANALYZE";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		echo "Finished!<BR>\n";
	}
	if ( $action == "Analyze TSyslog Table" ) {
		echo "Conducting Analyze of TSyslog....";
		$SQLQuery="ANALYZE TSyslog";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		echo "Finished!<BR>\n";
	}
	if ( $action == "Analyze Syslog_TArchive Table" ) {
		echo "Conducting Analyze of Syslog_TArchive....";
		$SQLQuery="ANALYZE Syslog_TArchive";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		echo "Finished!<BR>\n";
	}
	if ( $action == "FULL Vacuum Entire Database" ) {
		echo "Conducting Full Vacuum of Entire Database....";
		$SQLQuery="VACUUM FULL ANALYZE";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery); 
			die(pg_errormessage() . "<BR>\n");
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		echo "Finished!<BR>\n";
	}
	if ( ( $action == "View Unprocessed Log Breakdown" ) || ( $action == "View Archive Log Breakdown" ) ) {
		if ( $action == "View Unprocessed Log Breakdown" ) {
			$SQLQuery="select count(tsyslog_id), host from TSyslog group by host order by host";
		}
		if ( $action == "View Archive Log Breakdown" ) {
			$SQLQuery="select count(tsyslog_id), host from Syslog_TArchive group by host order by host";
		}
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
                      		die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows ) {
			echo "<TABLE COLS=2 BORDER=1><TR><TD width=1><B>Host</B></TD><TD width=1><B># of Records</B></TR>\n";
	 		for ( $loop = ($SQLNumRows - 1) ; $loop != -1 ; $loop-- ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
                                       	die(pg_errormessage()."<BR>\n");
					$HostID=$hostid = relatedata($dbsocket,"Syslog_THost","THost_ID","THost_Host='$SQLQueryResultsObject->host'");
				if ( $HostID > 0 ) {
					$HostProcessed=relatedata($dbsocket,"syslog_tprocessorprofile","THost_ID","THost_ID='$HostID'");
				} else {
					$HostProcessed=0;
				}
				if ( $HostID > 0 ) {
					if ( $HostProcessed > 0 ) {
						echo "<TR><TD>$SQLQueryResultsObject->host</TD><TD>$SQLQueryResultsObject->count</TD></TR>\n";
					} else {
						echo "<TR><TD><FONT COLOR=#FF8800>$SQLQueryResultsObject->host</FONT></TD><TD>$SQLQueryResultsObject->count</TD></TR>\n";
					}
				} else {
					echo "<TR><TD><FONT COLOR=#FF0000>$SQLQueryResultsObject->host</FONT></TD><TD>$SQLQueryResultsObject->count</TD></TR>\n";
				}
			}
			echo "</TABLE><BR>\n";
		}

		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}

	if ( $action == "Display Database Confguration" ) {
		echo "<B>$action</B><BR>\n";
		$SQLQuery="select * from pg_settings";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows ) {
			echo "<TABLE COLS=6 BORDER=1><TR><TD><B>Name</B></TD><TD align=center width=1><B>Setting</B></TD><TD align=center width=1><B>Context</B></TD><TD align=center width=1><B>Vartype</B></TD><TD><B>Source</B></TD><TD align=center width=1><B>Min_Val</B></TD><TD align=center width=1><B>Max_Val</B></TD></TR>\n";
			for ( $loop = ($SQLNumRows - 1) ; $loop != -1 ; $loop-- ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
                                       	die(pg_errormessage()."<BR>\n");
				echo "<TR><TD>$SQLQueryResultsObject->name</TD><TD align=center width=1>$SQLQueryResultsObject->setting</TD><TD align=center width=1>$SQLQueryResultsObject->context</TD><TD align=center width=1>$SQLQueryResultsObject->vartype</TD><TD>$SQLQueryResultsObject->source</TD><TD align=center width=1>$SQLQueryResultsObject->min_val</TD><TD align=center width=1>$SQLQueryResultsObject->max_val</TD></TR>\n";
			}
			echo "</TABLE><BR>\n";
		}
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		
	}
	if ( $action == "Display Current Locks" ) {
		echo "<B>$action</B><BR>\n";
		$SQLQuery="select * from pg_locks;";
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows ) {
			echo "<TABLE COLS=6 BORDER=1><TR><TD width=1><B>Relation</B></TD><TD width=1><B>Database</B></TD><TD width=1><B>Transaction</B></TD><TD width=1><B>PID</B></TD><TD width=1><B>Mode</B></TD><TD width=1><B>Granted</B></TD></TR>\n";
			for ( $loop = ($SQLNumRows - 1) ; $loop != -1 ; $loop-- ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
                                       	die(pg_errormessage()."<BR>\n");
				echo "<TR><TD width=1>$SQLQueryResultsObject->relation</TD><TD width=1>$SQLQueryResultsObject->database</TD><TD width=1>$SQLQueryResultsObject->transaction</TD><TD width=1>$SQLQueryResultsObject->pid</TD><TD width=1>$SQLQueryResultsObject->mode</TD><TD width=1>$SQLQueryResultsObject->granted</TD></TR>\n";
			}
			echo "</TABLE><BR>\n";
		}
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
		
	}

	if ( ( $action == "Display Index Usage" ) || ( $action == "Display Relavent Table Usage" ) || ( $action == "Display SMT Table Usage" ) ) {
		echo "<B>$action</B><BR>\n";
		$condition="";
		$total=0;
		if ( $action == "Display Index Usage" ) {
			$SQLQuery="SELECT c2.relname, c2.relpages, c2.relkind FROM pg_class c, pg_class c2, pg_index i where c.oid = i.indrelid AND c2.oid = i.indexrelid ORDER BY c2.relname";
			$title="Index Name";
		}
		if ( $action == "Display SMT Table Usage" ) {
			$SQLQuery="select relname, relpages,relkind  from pg_class where relkind='r' order by relname;";
			$condition = "syslog";
			$title="Table Name";
		}
		if ( $action == "Display Relavent Table Usage" ) {
			$SQLQuery="SELECT relname, relpages,relkind FROM pg_class ORDER BY relpages;";
			$title="Object Name";
		}
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
                      		die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows ) {
			echo "<TABLE COLS=2 BORDER=1><TR><TD width=1><B>$title</B></TD><TD width=1><B>Size(bytes)</B><TD width=1><B>Type</B></TD></TR>\n";
	 		for ( $loop = ($SQLNumRows - 1) ; $loop != -1 ; $loop-- ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
                                       	die(pg_errormessage()."<BR>\n");
				if ( $condition != "" ) {
					if ( ereg($condition,$SQLQueryResultsObject->relname) ) {
						echo "<TR><TD>$SQLQueryResultsObject->relname</TD><TD>" . number_format($SQLQueryResultsObject->relpages * 8192) . "</TD><TD align=center>";
						if ( $SQLQueryResultsObject->relkind == 'r' ) { echo "Table";}
						if ( $SQLQueryResultsObject->relkind == 'i' ) { echo "Index";}
						if ( $SQLQueryResultsObject->relkind == 'S' ) { echo "Sequence";}
						if ( $SQLQueryResultsObject->relkind == 'v' ) { echo "View";}
						if ( $SQLQueryResultsObject->relkind == 'c' ) { echo "Composite";}
						if ( $SQLQueryResultsObject->relkind == 's' ) { echo "Special";}
						if ( $SQLQueryResultsObject->relkind == 't' ) { echo "Toast";}
						echo "</TD></TR>\n";
						$total = $total + $SQLQueryResultsObject->relpages * 8192;
					}
				} else {
					echo "<TR><TD>$SQLQueryResultsObject->relname</TD><TD>" . number_format($SQLQueryResultsObject->relpages * 8192) . "</TD><TD align=center>";
					if ( $SQLQueryResultsObject->relkind == 'r' ) { echo "Table";}
					if ( $SQLQueryResultsObject->relkind == 'i' ) { echo "Index";}
					if ( $SQLQueryResultsObject->relkind == 'S' ) { echo "Sequence";}
					if ( $SQLQueryResultsObject->relkind == 'v' ) { echo "View";}
					if ( $SQLQueryResultsObject->relkind == 'c' ) { echo "Composite";}
					if ( $SQLQueryResultsObject->relkind == 's' ) { echo "Special";}
					if ( $SQLQueryResultsObject->relkind == 't' ) { echo "Toast";}
					echo "</TD></TR>\n";
					$total = $total + $SQLQueryResultsObject->relpages * 8192;
				}
			}
			echo "<TR><TD align=right><B>Total:</B></TD><TD>" . number_format($total) . "</TD></TR>\n";
			echo "</TABLE><BR>\n";
		}

		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	closeform();
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
