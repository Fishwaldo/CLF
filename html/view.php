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
		$header = "view.php?pagebreak=$pagebreak$appendurl&viewtype=$viewtype&pagenum=$pagenums&startdate=$startdate&enddate=$enddate&hostid=" . fixspace($hostid) . "&pagesize=$pagesize&datatype=$datatype&userid=$userid&typeid=$typeid&lastid=$lastid";
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
	$HeaderText="<font size=+1><B>Syslog Management</B></font><BR><BR>";
	$FooterText="<font face='Arial, Helvetica, sans-serif' size='-2'><BR>Version " . SMTVER . "<BR>&copy; Jeremy M. Guthrie All rights reserved.</font>\n";
	$PageTitle="Syslog Management Tool";

	if ( $group == 0 ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}

	if (($hostid != -1) && ( ! logincanseehost($dbsocket,$REMOTE_ID,$hostid) ) && ( $group == 1) ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}
	if ( ! isset($startdate) ) {
		$startdate=strtotime("$day-$month-$year $hour:$minute");
	}
	if ( $durtype == 1) {
		if (! isset($enddate) ) {
			$enddate = $startdate + $duration;
		}
	} if ( $durtype == 2 ) {
		if (! isset($enddate) ) {
			$enddate = strtotime("$eday-$emonth-$eyear $ehour:$eminute");
		}
	} 
	$month=date("M",$startdate);
	$year=date("Y",$startdate);
	$day=date("j",$startdate);
	$hour=date("G",$startdate);
	$minute=date("i",$startdate);

if (0) {
	if ( $viewtype == 2 ) {
echo "view2<br>";
		if ( ! isset($startdate) ) {
			for ( $loop = 1 ; $loop != 13 ; $loop++ ) {
				if ( $month == date("M",mktime(0,0,0,$loop,1,2002)) ) {
					$startdate=mktime($hour,$minute,0,$loop,$day,$year);
				}
			}
		} else {
echo "ehh<br>";
			$month=date("M",$startdate);
			$year=date("Y",$startdate);
			$day=date("j",$startdate);
			$hour=date("G",$startdate);
			$minute=date("i",$startdate);
		}
		$enddate=$startdate + $duration;
	}
}
	$time1=$hour . ":" . $minute . ":00";
	$date1=$month . "-" . $day . "-" . $year;
		
	$month2=date("M",$enddate);
	$year2=date("Y",$enddate);
	$day2=date("j",$enddate);
	$hour2=date("G",$enddate);
	$minute2=date("i",$enddate);
	
	$date2=$month2 . "-" . $day2 . "-" . $year2;
	$time2=$hour2 . ":" . $minute2 . ":00";
	$regexpcount=count($filterorlevel);
	$orig=$regexpcount;
	if ( isset($filter) && ( $filter == 1 ) && ( $filterid > 0 ) ) {
                $SQLQuery = "select * from Syslog_TFilterData where TFilter_ID=$filterid";
                $SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
                        die(pg_errormessage()."<BR>\n");
                $SQLNumRows = pg_numrows($SQLQueryResults);
		if ( $SQLNumRows ) {
			for ( $loop = ($SQLNumRows - 1) ; $loop != -1 ; $loop-- ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$regexp[$orig + $loop]=$SQLQueryResultsObject->tfilterdata_filter;
				$regexpinclude[$orig + $loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_include));
				$filterorlevel[$orig + $loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_filterorlevel));
				$startfacility[$orig + $loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_startfacility));
				$stopfacility[$orig + $loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_stopfacility));
				$startseverity[$orig + $loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_startseverity));
				$stopseverity[$orig + $loop]=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_stopseverity));
				$regexpcount++;
			}
		}
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage() . "<BR>\n");
	}
	if ( ( $action == "Save Results" ) and ( $savedesc == "" ) ) {
		$saveerr=1;
		$action="View";
	} else { $saveerr=0; }
	if ( ( $action == "Save Results" ) && ( $group >= 2 ) && ( $saveerr != 1 ) ) {
		$newtimestamp=time();
		$exptimestamp=$newtimestamp + ( 86400 * 30 ) ;
		$savedate=date("m-d-Y",$newtimestamp);
		$expdate=date("m-d-Y",$exptimestamp);
		$savetime=date("G:i:s",$newtimestamp);
		$savedesc=stripslashes($savedesc);
		$saveid=addsaveheader($dbsocket,$expdate,$savedesc,$savetime,$savedate,$REMOTE_ID);
	}

	if ( ( $action == "Save Filter" ) && ( strlen(pgdatatrim($filtername)) > 1 ) ) {
		if ( $group == 1 ) { $filtertype=1; }
		$filterid=addfilterheader($dbsocket,$filtertype,$filtername,$REMOTE_ID);
		for ( $loop = ($regexpcount - 1) ; $loop != -1 ; $loop-- ) {
			if ( ( ( strlen(pgdatatrim($regexp[$loop])) > 0 ) && ( $filterorlevel[$loop] <= 2 ) ) || ( $filterorlevel[$loop] == 3 ) ) { 
				addfilter($dbsocket,$regexp[$loop],$filterid,$regexpinclude[$loop],
					$filterorlevel[$loop],$startfacility[$loop],$stopfacility[$loop],
					$startseverity[$loop],$stopseverity[$loop]); 
			}
		}
	}
	do_header($PageTitle, 'view');
	if ( $durtype == 3 ) {
		$header = "view.php?pagebreak=$pagebreak$appendurl&durtype=$durtype&viewtype=$viewtype&pagenum=$pagenums&startdate=$startdate&enddate=$enddate&hostid=" . fixspace($hostid) . "&pagesize=$pagesize&datatype=$datatype&userid=$userid&typeid=$typeid&lastid=$lastid";
		echo "<meta http-equiv=\"refresh\" content=\"10;URL=".$header."\">";
	}
	formfield("viewtype","hidden",3,1,0,200,200,$viewtype);
	formfield("pagebreak","hidden",3,1,0,200,200,$pagebreak);
	formfield("pagesize","hidden",3,1,0,200,200,$pagesize);
	formfield("pagenum","hidden",3,1,0,200,200,1);
	formfield("hostid","hidden",3,1,0,200,200,$hostid);
	formfield("datatype","hidden",3,1,0,200,200,$datatype);
	formfield("timestamp","hidden",3,1,0,200,200,$startdate);
	if ( isset($userid) ) formfield("userid","hidden",3,1,0,200,200,$userid);
	formfield("typeid","hidden",3,1,0,200,200,$typeid);
	if ( isset($lastid) ) { formfield("lastid","hidden",3,1,0,200,200,$lastid); }
	if ( isset($regexpcount) ) {
		for ( $regloop = 0 ; $regloop != $regexpcount ; $regloop++ ) {
			formfield("regexp[]","hidden",3,1,0,200,200,stripslashes($regexp[$regloop]));
			formfield("regexpinclude[]","hidden",3,1,0,200,200,$regexpinclude[$regloop]);
			formfield("filterorlevel[]","hidden",3,1,0,200,200,$filterorlevel[$regloop]);
			formfield("startfacility[]","hidden",3,1,0,200,200,$startfacility[$regloop]);
			formfield("stopfacility[]","hidden",3,1,0,200,200,$stopfacility[$regloop]);
			formfield("startseverity[]","hidden",3,1,0,200,200,$startseverity[$regloop]);
			formfield("stopseverity[]","hidden",3,1,0,200,200,$stopseverity[$regloop]);
		}
	}

	$SQLQuery="";
	$TopSQLQuery="select distinct on (date,time,TSyslog_ID) TSyslog_ID, TSyslog.date, TSyslog.Time, TSyslog.host, TSyslog.message, TSyslog.Severity, TSyslog.Facility from TSyslog";
	$BottomSQLQuery="select distinct on (date,time,TSyslog_ID) Syslog_TArchive.TSyslog_ID, Syslog_TArchive.date, Syslog_TArchive.Time, Syslog_TArchive.host, Syslog_TArchive.message, Syslog_TArchive.Severity, Syslog_TArchive.Facility from Syslog_TArchive";
	if ( $datatype == 1 ) { 
		if ($hostid != -1) {
			$host=gethost($dbsocket,$hostid);
			$TopSQLQuery = $TopSQLQuery . " where host='$host'"; 
			$BottomSQLQuery = $BottomSQLQuery . " where host='$host'"; 
		} else {
			if ($group < 2) {
				$TopSQLQuery = $TopSQLQuery . ",Syslog_TCustomerProfile,Syslog_THost,Syslog_TProcessorProfile where Syslog_TCustomerProfile.TLogin_ID=$REMOTE_ID and TSyslog.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID "; 
				$BottomSQLQuery = $BottomSQLQuery . ",Syslog_THost,Syslog_TProcessorProfile where Syslog_TCustomerProfile.TLogin_ID=$REMOTE_ID and Syslog_TArchive.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID "; 
			} else {
				$TopSQLQuery = $TopSQLQuery . " ,Syslog_THost where TSyslog.host=Syslog_THost.THost_Host ";
				$BottomSQLQuery = $BottomSQLQuery . " ,Syslog_THost where Syslog_TArchive.host=Syslog_THost.THost_Host ";
			}
		}
			
	}
	if ( $datatype == 2 ) { 
		$TopSQLQuery = $TopSQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where TSyslog.host=Syslog_THost.THost_Host and TPremadeType_ID=$typeid"; 
		$BottomSQLQuery = $BottomSQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where Syslog_TArchive.host=Syslog_THost.THost_Host and TPremadeType_ID=$typeid"; 
	}
	if ( $datatype == 3 ) { 
		if (!isset($userid)) {
			die("No User Selected<br>");
		}	
		
		$TopSQLQuery = $TopSQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where Syslog_TCustomerProfile.TLogin_ID=$userid and TSyslog.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID"; 
		$BottomSQLQuery = $BottomSQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where Syslog_TCustomerProfile.TLogin_ID=$userid and Syslog_TArchive.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID"; 
	}
	if ( $datatype == 4 ) { 
		if (!isset($userid)) {
			die("No User Selected<br>");
		}	
		if (!isset($typeid)) {
			die("No Host Type Selected");
		}
		$TopSQLQuery = $TopSQLQuery . " ,Syslog_THost,Syslog_TCustomerProfile where " .
        		"( Syslog_TCustomerProfile.TLogin_ID=$userid ) and " .
        		"( TSyslog.host=Syslog_THost.THost_Host ) and " . 
			"( Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID ) and " .
        		"( Syslog_THost.TPremadeType_ID=$typeid )"; 
		$BottomSQLQuery = $BottomSQLQuery . " ,Syslog_THost,Syslog_TCustomerProfile where " .
        		"( Syslog_TCustomerProfile.TLogin_ID=$userid ) and " .
        		"( Syslog_TArchive.host=Syslog_THost.THost_Host ) and " . 
			"( Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID ) and " .
        		"( Syslog_THost.TPremadeType_ID=$typeid )"; 
	}
	if ( $durtype != 3 ) {
		if ( $date1 == $date2 ) {
			$SQLQueryDate="and date = '$date1' and ( time >= '$time1' and time <= '$time2')";
		} 
		if ( ( date("z",$enddate) - date("z",$startdate) ) == 1 ) { 
			$SQLQueryDate="and (( date = '$date1' and time >= '$time1' ) or " . 
				"( date = '$date2' and time <= '$time2' ) ) "; 
		}
		if ( ( date("z",$enddate) - date("z",$startdate) ) > 1 ) { 
			$SQLQueryDate="and (( date = '$date1' and time >= '$time1' ) or " .
				"( date > '$date1' and date < '$date2' ) or " .
				"( date = '$date2' and time <= '$time2' ) )"; 
		}
	}
	if ( $durtype == 3 ) {
		$SQLOrder = " desc";
	} else {
		$SQLOrder = "";
	}


	if ( isset($lastid) ) {
		$SQLQuery = $TopSQLQuery . "( TSyslog_ID > $lastid ) " . $SQLQueryDate . " union " . $BottomSQLQuery . " ( TSyslog_ID > $lastid ) " . $SQLQueryDate . " order by date ".$SQLOrder.", time". $SQLOrder.", TSyslog_ID". $SQLOrder;
	} else {
		$SQLQuery = $TopSQLQuery . $SQLQueryDate . " union " . $BottomSQLQuery . $SQLQueryDate . " order by date".$SQLOrder.", time". $SQLOrder.", TSyslog_ID". $SQLOrder;
	}

	$SQLQuery = $TopSQLQuery . $SQLQueryDate . " union " . $BottomSQLQuery . $SQLQueryDate . " order by date".$SQLOrder.", time". $SQLOrder.", TSyslog_ID". $SQLOrder;
	if ( ! isset($pagesize) ) { $pagesize=10; }
	if ( $durtype != 3) {
		$SQLQuery = $SQLQuery . " limit 5000";
	} else {
		$SQLQuery = $SQLQuery . " limit 100";
	}
	$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
		die(pg_errormessage().":".$SQLQuery."<BR>\n");
	$SQLNumRows = pg_numrows($SQLQueryResults);
	if ( $saveerr ) { echo "There was an error saving results<BR>\n"; }
	$linecount = 0;
	if ( $SQLNumRows > 0 ) {
		$lastid=0;
		if ( ! $pagenum ) { $pagenum=1; }
		$startline=$pagenum * $pagesize - $pagesize + 1 ;
		$stopline=$pagenum * $pagesize  ;
		$loop=0;
		$linecount=0;
		$lasttline=0;
		$keepgoing=1;
		$deliverymessage="";
		if ( ( $emailaddress != "" ) && ( $action == "EMail Results" ) ) { 
			$deliverymessage="Report from $REMOTE_USER\n\n"; 
		}
		echo "<TABLE BORDER=2>\n";
		echo "<TR><TD NOWRAP><B>ID</B></TD><TD NOWRAP><B>Date</B></TD><TD NOWRAP><B>Time</B></TD><TD NOWRAP><B>Facility</B></TD><TD NOWRAP><B>Severity</B></TD><TD NOWRAP><B>Host</B></TD><TD><B>Message</B></TD></TR>\n";
		$newhost="";	
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$id=stripslashes(pgdatatrim($SQLQueryResultsObject->tsyslog_id));
			$date=stripslashes(pgdatatrim($SQLQueryResultsObject->date));
			$time=stripslashes(pgdatatrim($SQLQueryResultsObject->time));
			$host=stripslashes(pgdatatrim($SQLQueryResultsObject->host));
			$message=stripslashes(pgdatatrim($SQLQueryResultsObject->message));
			$severity=stripslashes(pgdatatrim($SQLQueryResultsObject->severity));
			$facility=stripslashes(pgdatatrim($SQLQueryResultsObject->facility));
			$vseverity=verboseseverity(stripslashes(pgdatatrim($SQLQueryResultsObject->severity)));
			$vfacility=verbosefacility(stripslashes(pgdatatrim($SQLQueryResultsObject->facility)));
			if ( $host != $newhost ) {
				if ( $newhost != "" ) {
					pg_freeresult($RuleResults) or
						die(pg_errormessage() . "<BR>\n");
				}
				$hostid=gethostid($dbsocket,$host);
				$SQLQuery="select * from Syslog_TRule where THost_ID=$hostid";
				$RuleResults = pg_exec($dbsocket,$SQLQuery) or
					die(pg_errormessage()."<BR>\n");
				$NumOfRules = pg_numrows($RuleResults); 
				$newhost=$host;
			}
		
			$messagecolor="<font color=#000000>";
			for ( $loop1 = 0 ; $loop1 != $NumOfRules ; $loop1++ ) {
				$RuleResultsObject = pg_fetch_object($RuleResults,$loop1) or
					die(pg_errormessage()."<BR>\n");

				$ruleruleorlevel=pgdatatrim($RuleResultsObject->trule_ruleorlevel);
				$rulestartfacility=pgdatatrim($RuleResultsObject->trule_startfacility);
				$rulestopfacility=pgdatatrim($RuleResultsObject->trule_stopfacility);
				$rulestartseverity=pgdatatrim($RuleResultsObject->trule_startseverity);
				$rulestopseverity=pgdatatrim($RuleResultsObject->trule_stopseverity);

				if ( strlen(pgdatatrim($RuleResultsObject->trule_expression)) > 0 ) { 
					$regresults=ereg(pgdatatrim($RuleResultsObject->trule_expression),$message);
				} else {
					$regresults="";
				}
                                $bounds=withinbounds($facility,$severity,$rulestartfacility,$rulestopfacility,$rulestartseverity,$rulestopseverity);
				$color="off";
                                if ( ( ( $ruleruleorlevel == 1 ) && ( $regresults ) ) ||
                                        ( ( $ruleruleorlevel == 2 ) && ( $regresults ) && ( $bounds ) ) ||
                                        ( ( $ruleruleorlevel == 3 ) && ( $bounds ) ) ) {
					$messagecolor="<font color=#FF0000>";
				}
			} 
			$clear=1;
			if ( isset($regexpcount) ) { 
				for ( $regloop = $regexpcount - 1 ; $regloop != -1 ; $regloop-- ) {
					if ( $clear ) {
						$rule=1;
						$level=1;
						if ( $filterorlevel[$regloop] <= 2 ) { 
							if ( $regexp[$regloop] != "" ) {
								if ( ( $regexpinclude[$regloop] == "0" ) && ( ereg($regexp[$regloop],$message) ) ) { $rule=0; }
								if ( ( $regexpinclude[$regloop] == "1" ) && ( ! ereg($regexp[$regloop],$message) ) ) { $rule=0; }
							}
						}
						if ( $filterorlevel[$regloop] >= 2 ) { 
							if ( $regexpinclude[$regloop] == "0" ) { 
								if ( withinbounds($facility,$severity,$startfacility[$regloop],$stopfacility[$regloop],
									$startseverity[$regloop],$stopseverity[$regloop]) ) { $level=0; }; 
							}
							if ( $regexpinclude[$regloop] == "1" ) {
								if ( ! withinbounds($facility,$severity,$startfacility[$regloop],$stopfacility[$regloop],
									$startseverity[$regloop],$stopseverity[$regloop]) ) { $level=0; }; 
							}
						}
						if ( $filterorlevel[$regloop] == 1 ) { $clear = $rule; }
						if ( ( $filterorlevel[$regloop] == 2 ) && ( ( $rule != 1 ) || ( $level != 1 ) ) ) { $clear = 0 ; } 
						if ( $filterorlevel[$regloop] == 3 ) { $clear = $level; }
					}
				}
			}
			if ( isset($clear) && ($clear > 0) ) { 
				$linecount++;
				if ( $bgcolor == "#EEEEEE" ) { $bgcolor = "#FFFFFF"; } else { $bgcolor = "#EEEEEE";}
				if ( ( ( $pagebreak ) && ( $linecount >= $startline ) && ( $linecount <= $stopline ) ) || ( ! $pagebreak ) ) {
					$fontcolor="#000000";
					if ( ( $severity == 4 ) || ( $severity == 3 ) ) { $fontcolor='#FF8800'; } 
					if ( $severity <= 2 ) { $fontcolor='#FF0000'; } 
					echo "<TR bcolor=$bgcolor><TD NOWRAP>$id</TD><TD NOWRAP>$date</TD><TD NOWRAP>$time</TD><TD NOWRAP>$vfacility</TD><TD NOWRAP><FONT COLOR=$fontcolor>$vseverity</FONT></TD><TD NOWRAP>$host</TD><TD>$messagecolor".htmlspecialchars($message)."</font>";
					echo "</TD></tr>\n"; 
					$lastline=$linecount;
				}
				if ( ( $group >= 2 ) && ( $saveid != 0 ) && ( $action == "Save Results" ) && ( $saverr != 1 ) ) {
					savefilteredview($dbsocket,$saveid,$date,$time,$host,$facility,$severity,$message);
				} 
				if ( ( $emailaddress != "" ) && ( $action == "EMail Results" ) ) { 
					$deliverymessage=$deliverymessage . "$date $time $host $vfacility $vseverity  $message\r\n";
				}
			}
		}
		if ( ( $pagebreak ) ) {	
			$appendurl="";
			if ( $regexpcount ) {
				for ( $regloop = 0 ; $regloop != $regexpcount ; $regloop++ ) {
					$appendurl=$appendurl."&regexp%5B%5D=" . urlencode(htmlspecialchars($regexp[$regloop],ENT_QUOTES)) . "&regexpinclude%5B%5D=$regexpinclude[$regloop]&filterorlevel%5B%5D=$filterorlevel[$regloop]&startfacility%5B%5D=$startfacility[$regloop]&stopfacility%5B%5D=$stopfacility[$regloop]&startseverity%5B%5D=$startseverity[$regloop]&stopseverity%5B%5D=$stopseverity[$regloop]";
				}
			}
			echo "<TR><TD>";
			if ( $startline > 1 ) {
				$pagenums=$pagenum-1;
				echo "<a href=view.php?pagebreak=$pagebreak&viewtype=$viewtype&pagenum=$pagenums&startdate=$startdate&enddate=$enddate&hostid=" . fixspace($hostid) . "&duration=$duration&pagesize=$pagesize$appendurl&datatype=$datatype&userid=$userid&typeid=$typeid><B>Previous</B></a></TD><TD></TD>";
			} else {
				echo "</TD><TD></TD>";
			}
			if ( $linecount > $lastline ) {
				$pagenums=$pagenum+1;
				echo "<TD><a href=view.php?pagebreak=$pagebreak$appendurl&viewtype=$viewtype&pagenum=$pagenums&startdate=$startdate&enddate=$enddate&hostid=" . fixspace($hostid) . "&pagesize=$pagesize&datatype=$datatype&userid=$userid&typeid=$typeid><B>Next</B></a></TD></TR>";
			} else {
				echo "</TR>";
			}
		}
	}
	echo "</TABLE>\n";
	echo "<B>$linecount Lines available after filtering $SQLNumRows lines</b><br>";
	if ( $SQLNumRows == 5000 ) { 
		echo "<font size='+1'>Last entry:  $time $date</font><BR>\n";
		echo "Please click <A href=view.php?pagebreak=$pagebreak$appendurl&viewtype=$viewtype&pagenum=$pagenums&startdate=$startdate&enddate=$enddate&hostid=" . fixspace($hostid) . "&pagesize=$pagesize&datatype=$datatype&userid=$userid&typeid=$typeid&lastid=$lastid>Here</a> to view the next 5000 lines.<BR>\n";
		
	}
	if ($durtype != 3) {
		if ( ( isset($emailaddress)) && ( $action == "EMail Results" ) ) { 
			$results=mail($emailaddress,"SMT EMail",$deliverymessage);	
			if ( $results ) { echo "Email sent to SMTP server<BR>\n"; }
		}
		echo "EMail Address:  ";
		formfield("emailaddress","text",3,1,0,40,128);
		formsubmit("EMail Results",3,1,1);
		if ( $action == "Save Results" ) {
			echo "<B><FONT COLOR=#FF0000>Saved Results</FONT></B><BR>\n";
		}
		if ( $group >= 2 ) {
			echo "Description for Saved Results:  ";
			formfield("savedesc","text",3,1,0,40,128);
			formsubmit("Save Results",3,1,1);
		}
	}
	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	formfield("timestamp","hidden",3,1,0,200,200,$startdate);
	formfield("duration","hidden",3,1,0,200,200,$duration);
	closeform();
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
