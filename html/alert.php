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
	
	if ( ( $group == 1) && ( $viewtype != 1 ) && ( $datatype == 1) ) {
		if ( ! logincanseehost($dbsocket,$REMOTE_ID,$hostid) ) {
			echo "BYE<BR>\n";
			dbdisconnect($sec_dbsocket);
			dbdisconnect($dbsocket);
			exit;
		}
	}

	/***************************************************************************/
	/* Add aggregate interface:                                                */
	/* select count(tsyslog_id), host from TSyslog group by host order by host */
	/***************************************************************************/

	if ( $group == 1 ) {	
		$userid=$REMOTE_ID;
	}

	if ( ( $group == 1 ) && ( $viewtype == 2 ) && ( $datatype  == 2 ) ) {
		$datatype = 4;
		$userid=$REMOTE_ID;
	}
	if ( $viewtype == 1 ) {
		if ( ! $aggregate ) {
			$SQLQuery="select TSyslog.TSyslog_id,Syslog_TAlert.TAlert_Date,Syslog_TAlert.TAlert_Time,Syslog_TAlert.TAlert_Info,TSyslog.date,TSyslog.time,TSyslog.host,TSyslog.message,TSyslog.Facility,TSyslog.Severity from TSyslog,Syslog_TAlert where Syslog_TAlert.TAlert_Date='$month-$day-$year' and Syslog_TAlert.TSyslog_id=TSyslog.TSyslog_id union select Syslog_TArchive.TSyslog_id,Syslog_TAlert.TAlert_Date,Syslog_TAlert.TAlert_Time,Syslog_TAlert.TAlert_Info,Syslog_TArchive.date,Syslog_TArchive.time,Syslog_TArchive.host,Syslog_TArchive.message,Syslog_TArchive.Facility,Syslog_TArchive.Severity from Syslog_TArchive,Syslog_TAlert where Syslog_TAlert.TAlert_Date='$month-$day-$year' and Syslog_TAlert.TSyslog_id=Syslog_TArchive.TSyslog_id order by date,time desc"; 
		} else {
			$SQLQuery="select tsyslog.host, count(distinct(TSyslog.TSyslog_id)) from TSyslog,Syslog_TAlert where Syslog_TAlert.TAlert_Date='$month-$day-$year' and Syslog_TAlert.TSyslog_id=TSyslog.TSyslog_id group by host union select syslog_tarchive.host,count(distinct(syslog_tarchive.TSyslog_id)) from Syslog_TArchive,Syslog_TAlert where Syslog_TAlert.TAlert_Date='$month-$day-$year' and Syslog_TAlert.TSyslog_id=Syslog_TArchive.TSyslog_id group by host order by host"; 
		}
	} else {
		$SQLQuery="";
		if ( ! $aggregate ) {
			$TopSQLQuery="select TSyslog.TSyslog_id, Syslog_TAlert.TAlert_Date, Syslog_TAlert.TAlert_Time, Syslog_TAlert.TAlert_Info, TSyslog.date, TSyslog.time, TSyslog.host, TSyslog.message, TSyslog.Facility, TSyslog.Severity from TSyslog, Syslog_TAlert";
			$BottomSQLQuery="select Syslog_TArchive.TSyslog_id, Syslog_TAlert.TAlert_Date, Syslog_TAlert.TAlert_Time, Syslog_TAlert.TAlert_Info, Syslog_TArchive.date, Syslog_TArchive.time, Syslog_TArchive.host, Syslog_TArchive.message, Syslog_TArchive.Facility, Syslog_TArchive.Severity from Syslog_TArchive, Syslog_TAlert";
		} else {
			$TopSQLQuery="select tsyslog.host, count(distinct(TSyslog.TSyslog_id)) from TSyslog, Syslog_TAlert";
			$BottomSQLQuery="select syslog_tarchive.host, count(distinct(Syslog_TArchive.TSyslog_id)) from Syslog_TArchive, Syslog_TAlert";
		}
	        if ( $datatype == 1 ) {
	                $host=gethost($dbsocket,$hostid);
	                $TopSQLQuery = $TopSQLQuery . ",Syslog_THost where TSyslog.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=$hostid and Syslog_TAlert.TSyslog_id=TSyslog.TSyslog_id and Syslog_TAlert.TAlert_Date='$month-$day-$year' ";
			if ( $aggregate ) {
				$TopSQLQuery = $TopSQLQuery . " group by host union ";
			} else {
				$TopSQLQuery = $TopSQLQuery . " union ";
			}
	                $BottomSQLQuery = $BottomSQLQuery . ",Syslog_THost where Syslog_TArchive.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=$hostid and Syslog_TAlert.TSyslog_id=Syslog_TArchive.TSyslog_id and Syslog_TAlert.TAlert_Date='$month-$day-$year' ";
			if ( $aggregate ) {
				$BottomSQLQuery = $BottomSQLQuery . " group by host order by host";
			} else {
				$BottomSQLQuery = $BottomSQLQuery . " order by date,time desc";
			}
			$SQLQuery=$TopSQLQuery . $BottomSQLQuery;
	        }
	        if ( $datatype == 2 ) {
	                $TopSQLQuery = $TopSQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where TSyslog.host=Syslog_THost.THost_Host and TPremadeType_ID=$typeid and Syslog_TAlert.TAlert_Date='$month-$day-$year' and Syslog_TAlert.TSyslog_id=TSyslog.TSyslog_id ";
			if ( $aggregate ) {
				$TopSQLQuery = $TopSQLQuery . " group by host union ";
			} else {
				$TopSQLQuery = $TopSQLQuery . " union ";
			}
	                $BottomSQLQuery = $BottomSQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where Syslog_TArchive.host=Syslog_THost.THost_Host and TPremadeType_ID=$typeid and Syslog_TAlert.TAlert_Date='$month-$day-$year' and Syslog_TAlert.TSyslog_id=Syslog_TArchive.TSyslog_id ";
			if ( $aggregate ) {
				$BottomSQLQuery = $BottomSQLQuery . " group by host order by host";
			} else {
				$BottomSQLQuery = $BottomSQLQuery . " order by date,time desc";
			}
			$SQLQuery=$TopSQLQuery . $BottomSQLQuery;
	        }
	        if ( $datatype == 3 ) {
	                $TopSQLQuery = $TopSQLQuery . " ,Syslog_THost,Syslog_TCustomerProfile where " .
				"( Syslog_TAlert.TSyslog_id=TSyslog.TSyslog_id ) and ".
	                        "( Syslog_TCustomerProfile.TLogin_ID=$userid ) and " .
	                        "( TSyslog.host=Syslog_THost.THost_Host ) and " .
	                        "( Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID ) and " .
				"( Syslog_TAlert.TAlert_Date='$month-$day-$year' ) ";
			if ( $aggregate ) {
				$TopSQLQuery = $TopSQLQuery . " group by host union ";
			} else {
				$TopSQLQuery = $TopSQLQuery . " union ";
			}
	                $BottomSQLQuery = $BottomSQLQuery . " ,Syslog_THost,Syslog_TCustomerProfile where " .
				"( Syslog_TAlert.TSyslog_id=Syslog_TArchive.TSyslog_id ) and ".
	                        "( Syslog_TCustomerProfile.TLogin_ID=$userid ) and " .
	                        "( Syslog_TArchive.host=Syslog_THost.THost_Host ) and " .
	                        "( Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID ) and " .
				"( Syslog_TAlert.TAlert_Date='$month-$day-$year' ) ";
			if ( $aggregate ) {
				$BottomSQLQuery = $BottomSQLQuery . " group by host order by host";
			} else { 
				$BottomSQLQuery = $BottomSQLQuery . " order by date,time desc";
			}
			$SQLQuery=$TopSQLQuery . $BottomSQLQuery;
	        }
	        if ( $datatype == 4 ) {
	                $TopSQLQuery = $TopSQLQuery . " ,Syslog_THost,Syslog_TCustomerProfile where " .
				"( Syslog_TAlert.TSyslog_id=TSyslog.TSyslog_id ) and ".
	                        "( Syslog_TCustomerProfile.TLogin_ID=$userid ) and " .
	                        "( TSyslog.host=Syslog_THost.THost_Host ) and " .
	                        "( Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID ) and " .
	                        "( Syslog_THost.TPremadeType_ID=$typeid ) and ".
				"( Syslog_TAlert.TAlert_Date='$month-$day-$year' ) "; 
			if ( $aggregate ) {
				$TopSQLQuery = $TopSQLQuery . " group by host union ";
			} else { 
				$TopSQLQuery = $TopSQLQuery . " union ";
			}
	                $BottomSQLQuery = $BottomSQLQuery . " ,Syslog_THost,Syslog_TCustomerProfile where " .
				"( Syslog_TAlert.TSyslog_id=Syslog_TArchive.TSyslog_id ) and ".
	                        "( Syslog_TCustomerProfile.TLogin_ID=$userid ) and " .
	                        "( Syslog_TArchive.host=Syslog_THost.THost_Host ) and " .
	                        "( Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID ) and " .
	                        "( Syslog_THost.TPremadeType_ID=$typeid ) and ".
				"( Syslog_TAlert.TAlert_Date='$month-$day-$year' ) " ; 
			if ( $aggregate ) {
				$BottomSQLQuery = $BottomSQLQuery . " group by host order by host";
			} else { 
				$BottomSQLQuery = $BottomSQLQuery . " order by date,time desc";
			}
			$SQLQuery=$TopSQLQuery . $BottomSQLQuery;
	        }
	}


	/* Create the 'previous' and 'next' day date parameters */
	$todayseconds=mktime(12,0,0,numberofmonth($month),$day,$year);
	$priorday=$todayseconds - 86400;
	$nextday=$todayseconds + 86400; 

	$pmonth=strftime("%b",$priorday);
	$pday=strftime("%d",$priorday);
	$pyear=strftime("%Y",$priorday);

	$nmonth=strftime("%b",$nextday);
	$nday=strftime("%d",$nextday);
	$nyear=strftime("%Y",$nextday);

	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage()."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	$PageTitle="Syslog Management Tool";
	do_header($PageTitle, 'alert');

	if ( $aggregate ) {
		$numhosts = 0;
		$hosts = "";
		$alerttotal=0;
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$found = 0;
			for ( $subloop = 1 ; $subloop != ($numhosts + 1 ) ; $subloop++ ) {
				if ( $SQLQueryResultsObject->host == $hosts[$subloop] ) { 
					$found++; 
					$count[$subloop] = $count[$subloop] + $SQLQueryResultsObject->count;
				} 
			}	
			if ( ! $found ) {
				$numhosts++;
				$hosts[$numhosts]=$SQLQueryResultsObject->host;
				$count[$numhosts]=$SQLQueryResultsObject->count;	
				$alerttotal = $alerttotal + $SQLQueryResultsObject->count;
			}
		}
	}
	echo "<B>Date:</B>  $month-$day-$year<BR><BR>\n";
	if ( $viewtype == 1 ) {
		echo "<TABLE BORDER=2 cols=2>\n<TR><TD><A HREF='alert.php?month=$pmonth&day=$pday&year=$pyear&viewtype=$viewtype&aggregate=$aggregate'>Previous Day</A></TD>" . 
			"<TD><A HREF='alert.php?month=$month&day=$day&year=$year&viewtype=$viewtype&aggregate=$aggregate'>Refresh</A></TD>".
			"<TD><A HREF='alert.php?month=$nmonth&day=$nday&year=$nyear&viewtype=$viewtype&aggregate=$aggregate'>Next Day</A></TD></TR></TABLE><BR>\n";
	}
	if ( $viewtype == 2 ) {
		$append="&viewtype=$viewtype&datatype=$datatype&hostid=$hostid&typeid=$typeid&&userid=$userid&aggregate=$aggregate";
		echo "<TABLE BORDER=2 cols=2>\n<TR><TD><A HREF='alert.php?month=$pmonth&day=$pday&year=$pyear$append'>Previous Day</A></TD>" . 
			"<TD><A HREF='alert.php?month=$month&day=$day&year=$year&viewtype=$viewtype$append'>Refresh</A></TD>".
			"<TD><A HREF='alert.php?month=$nmonth&day=$nday&year=$nyear&viewtype=$viewtype$append'>Next Day</A></TD></TR></TABLE><BR>\n";
	}
	if ( $SQLNumRows ) {
		if ( ! $aggregate ) { 
			echo "<TABLE BORDER=2 cols=9>\n";
			echo "<TR><TD width=70>Syslog ID</TD><TD width=100>Alarm Date</TD><TD width=70>Alarm Time</TD><TD width=100>Learned Date</TD><TD width=70>Learned Time</TD><TD width=100>Facility</TD><TD width=100>Severity</TD><TD width=100>Host</TD><TD width=100>Alert Rule</TD></TR>\n";

			for ( $loop=0 ; $loop != $SQLNumRows ; $loop++ ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				if ( $bgcolor == "#EEEEEE" ) { $bgcolor = "#FFFFFF"; } else { $bgcolor = "#EEEEEE";}
				$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tsyslog_id));
				$date=stripslashes(pgdatatrim($SQLQueryResultsObject->date));
				$time=stripslashes(pgdatatrim($SQLQueryResultsObject->time));
				$host=stripslashes(pgdatatrim($SQLQueryResultsObject->host));
				$message=stripslashes(pgdatatrim($SQLQueryResultsObject->message));
				$alertdate=stripslashes(pgdatatrim($SQLQueryResultsObject->talert_date));
				$alerttime=stripslashes(pgdatatrim($SQLQueryResultsObject->talert_time));
				$alertinfo=stripslashes(pgdatatrim($SQLQueryResultsObject->talert_info));
				$severity=stripslashes(pgdatatrim($SQLQueryResultsObject->severity));
				$facility=stripslashes(pgdatatrim($SQLQueryResultsObject->facility));
				$fontcolor='#000000';
				if ( ( $severity == 4 ) || ( $severity == 3 ) ) { $fontcolor='#FF8800'; }
				if ( $severity <= 2 ) { $fontcolor='#FF0000'; }
				$severity=verboseseverity($severity);
				$facility=verbosefacility($facility);
	
				echo "<TR BGCOLOR=$bgcolor><TD width=70>$id</TD><TD width=100>$alertdate</TD><TD width=70>$alerttime</TD><TD width=100>$date</TD><TD width=70>$time</TD><TD width=100>$facility</TD><TD width=100><font color=$fontcolor>$severity</font></TD><TD width=100>$host</TD><TD width=100><pre>$alertinfo</pre></TD></TR>\n";
				echo "<TR><TD COLSPAN=9><pre>$message</pre></TD></TR>\n";
			}
			echo "</table>\n";
		} else {
			echo "<TABLE BORDER=2 cols=2>\n";
			echo "<TR><TD><B>Host Name</B></TD><TD><B># of Alerts</B></TD></TR>\n"; 
			for ( $loop = 1 ; $loop != ($numhosts+1) ; $loop ++ ) {
				$hostid=relatedata($dbsocket,"Syslog_THost","THost_ID","THost_Host='$hosts[$loop]'");
				$href="alert.php?viewtype=2&datatype=1&hostid=$hostid&typeid=6&month=$month&day=$day&year=$year&aggregate=0&action=View";
				echo "<TR><TD><a href='$href'>$hosts[$loop]</A></TD><TD>$count[$loop]</TD></TR>\n";
			}
			echo "<TR><TD ALIGN=RIGHT><B>Total:</B></TD><TD><B>$alerttotal alerts</B></TD></TR>\n";
			echo "</table>\n";
		}
	} else { echo "No alerts for given day.<BR><BR>\n"; }
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
