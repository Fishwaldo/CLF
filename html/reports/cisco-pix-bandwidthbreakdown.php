<%
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
	if ( $SERVER_PORT != 443 ) {
		echo "This page must be accessed with SSL<BR>\n";
		exit;
	}
	require_once('/opt/apache/htdocs/login/lib/pgsql.php');
	require_once('/opt/apache/htdocs/login/lib/generalweb.php');
	require_once('/opt/apache/htdocs/login/lib/secframe.php');
	require_once('/opt/apache/htdocs/login/lib/pix.php');

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

	$HeaderText="<font size=+1><B>Reports</B></font><BR><BR>\n";
	$FooterText="<font face='Arial, Helvetica, sans-serif' size='-2'><BR>Version " . SMTVER . "<BR>&copy; Jeremy M. Guthrie All rights reserved.</font>\n";
	$PageTitle="Syslog Management Tool";

	$totalrows=0;
	$ftpcount=0;
	$ftp=0;
	$httpcount=0;
	$http=0;
	$https=0;
	$httpscount=0;
	$dnsudpcount=0;
	$dnsudp=0;
	$dnstcpcount=0;
	$dnstcp=0;
	$telnet=0;
	$telnetcount=0;
	$ssh=0;
	$sshcount=0;

	/* Port 25 tcp */
	$smtp=0;
	$smtpcount=0;

	/* Port 465 tcp */
	$smtps=0;
	$smtpscount=0;

	/* Port 161 udp */
	$snmp=0;
	$snmpcount=0;

	/* Port 162 udp */
	$snmptrap=0;
	$snmptrapcount=0;

	$gopher=0;
	$gophercount=0;

	/* Port 110 tcp */
	$pop3=0;
	$pop3count=0;

	/* Port 995 tcp */
	$pop3s=0;
	$pop3scount=0;

	$nntp=0;
	$nntpcount=0;
	$ntp=0;
	$ntpcount=0;

	/* 69 udp */
	$tftp=0;
	$tftpcount=0;

	/* Port 143 */
	$imap=0;
	$imapcount=0;

	/* Port 993 */
	$imaps=0;
	$imapscount=0;

	/* Port 135 */
	$locservudp=0;
	$locservudpcount=0;
	$locservtcp=0;
	$locservtcpcount=0;

	/* Port 137 */
	$netbiosnsudp=0;
	$netbiosnsudpcount=0;
	$netbiosnstcp=0;
	$netbiosnstcpcount=0;

	/* Port 138 */
	$netbiosdgmudp=0;
	$netbiosdgmudpcount=0;
	$netbiosdgmtcp=0;
	$netbiosdgmtcpcount=0;

	/* Port 139 */
	$netbiosssnudp=0;
	$netbiosssnudpcount=0;
	$netbiosssntcp=0;
	$netbiosssntcpcount=0;

	/* Other */
	$othertcp=0;
	$othertcpcount=0;
	$otherudp=0;
	$otherudpcount=0;
	$other=0;
	$othercount=0;

	$goodrows=0;
	if ( ( $group < 2 ) && ( $datatype > 3 ) ) { $datatype = 1; }

        $time1=$hour . ":" . $minute . ":00";
        $date1=$month . "-" . $day . "-" . $year;
        $date2=$month2 . "-" . $day2 . "-" . $year2;
        $time2=$hour2 . ":" . $minute2 . ":00";

	for ( $loop = 1 ; $loop != 13 ; $loop++ ) {
		if ( $month == date("M",mktime(0,0,0,$loop,1,2002)) ) {
			$timestamp=mktime($hour,$minute,0,$loop,$day,$year);
		}
	}
	for ( $loop = 1 ; $loop != 13 ; $loop++ ) {
		if ( $month2 == date("M",mktime(0,0,0,$loop,1,2002)) ) {
			$timestamp2=mktime($hour2,$minute2,0,$loop,$day2,$year2);
		}
	}

	$BaseSQLQuery="select TSyslog_ID, TSyslog.date, TSyslog.Time, TSyslog.host, TSyslog.message, TSyslog.Severity, TSyslog.Facility from TSyslog";
	$alldata=0;

%>
<HTML>
	<HEAD>
		<TITLE>
<%	echo $PageTitle; %>
		</TITLE>
	</HEAD>
<%
	$firsttimethrough=1;

	while ( ! $alldata ) {
		$SQLQuery = $BaseSQLQuery;
		if ( $datatype == 1 ) {
			$host=gethost($dbsocket,$hostid);
			$SQLQuery = $SQLQuery . " where host='$host' and ";
		}
		if ( $datatype == 2 ) {
			$SQLQuery = $SQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where TSyslog.host=Syslog_THost.THost_Host and TPremadeType_ID=$typeid and ";
		}
		if ( $datatype == 3 ) {
			$SQLQuery = $SQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where Syslog_TCustomerProfile.TLogin_ID=$userid and TSyslog.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID and ";
		}
		if ( $datatype == 4 ) {
			$SQLQuery = $SQLQuery . " ,Syslog_THost,Syslog_TCustomerProfile where " .
				"( Syslog_TCustomerProfile.TLogin_ID=$userid ) and " .
				"( TSyslog.host=Syslog_THost.THost_Host ) and " .
				"( Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID ) and " .
				"( Syslog_THost.TPremadeType_ID=$typeid ) and ";
		}
		if ( ! $firsttimethrough ) {
			$date1=$newstartdate;
			$time1=$newstarttime;
			$temphour=substr($time1,0,2);
			$tempmin=substr($time1,3,2);
			$tempsec=substr($time1,6,2);
			$tempyear=substr($date1,0,4);
			$tempmonth=substr($date1,5,2);
			$tempday=substr($date1,8,2);
			$tempday=$tempday + 1 ;
			$tempday=$tempday - 1 ;
			$timestamp=mktime($temphour,$tempmin,$tempsec,$tempmonth,$tempday,$tempyear);
		 	$tempmonth = date("M",mktime(0,0,0,$tempmonth,1,2002));

			$date1=$tempmonth . "-" . $tempday . "-" . $tempyear;
		}

		if ( $date1 == $date2 ) {
			$SQLQueryDate="date = '$date1' and ( time >= '$time1' and time <= '$time2')";
		}
	
		if ( ( ( date("z",$timestamp2) - date("z",$timestamp) ) == 1 ) && ( $year1 == $year2 ) ) {
			$SQLQueryDate="( ( date = '$date1' and time >= '$time1' ) or " .
				"( date = '$date2' and time <= '$time2' ) ) ";
		}
		if ( ( date("z",$timestamp2) - date("z",$timestamp) ) > 1 ) {
			$SQLQueryDate=" ( ( date = '$date1' and time >= '$time1' ) or " .
				"( date > '$date1' and date < '$date2' ) or " .
				"( date = '$date2' and time <= '$time2' ) )";
		}
		if ( ! $firsttimethrough ) {
			$SQLQuery = $SQLQuery . $SQLQueryDate . " and tsyslog_id > $lastid order by date, time, TSyslog_ID limit 50";
		} else {
			$SQLQuery = $SQLQuery . $SQLQueryDate . " order by date, time, TSyslog_ID limit 50";
		}	

		echo " ";

		if ( $timestamp <= $timestamp2 ) {
			echo "SQL Query:  $SQLQuery<BR>\n";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
		} else {
			$SQLNumRows = 0;
		}
		$totalrows = $totalrows + $SQLNumRows;
		if ( ( $SQLNumRows == 0 ) || ( $SQLNumRows < 50 ) ) { 
			$alldata = 1 ; 
		} else {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,49) or
				die(pg_errormessage()."<BR>\n");
			$newstartdate=pgdatatrim($SQLQueryResultsObject->date);
			$newstarttime=pgdatatrim($SQLQueryResultsObject->time);
			$lastid=pgdatatrim($SQLQueryResultsObject->tsyslog_id);
		}
		$firsttimethrough=0;
	
	
		if ( $SQLNumRows != 0 ) {
			for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$host=pgdatatrim($SQLQueryResultsObject->host);
				$message=pgdatatrim($SQLQueryResultsObject->message);
				$date=pgdatatrim($SQLQueryResultsObject->date);
				$time=pgdatatrim($SQLQueryResultsObject->time);
				if ( $reporttype == 3 ) { 
					if ( ereg("^%PIX-6-302002",$message) ) {
						$goodrows++;
						$stringtoken = strtok($message," \\");
						$count=0;
						while ( $stringtoken ) {
							$count++;
	    						$stringtoken = strtok(" \\");
							switch ($count) {
								case 2:
									$protocol=$stringtoken;
							        	break;
	    							case 6:
									$faddr=$stringtoken;
									$faddrport=substr(strstr($faddr,'/'),1);
									$faddr=substr($faddr,0,(strlen($faddr) - (strlen($faddrport) + 1)));
									break;
	    							case 8:
									$gaddr=$stringtoken;
									$gaddrport=substr(strstr($gaddr,'/'),1);
									$gaddr=substr($gaddr,0,(strlen($gaddr) - (strlen($gaddrport) + 1)));
									break;
	    							case 10:
									$laddr=$stringtoken;
									$laddrport=substr(strstr($laddr,'/'),1);
									$laddr=substr($laddr,0,(strlen($laddr) - (strlen($laddrport) + 1)));
									break;
	    							case 12:
									$duration=$stringtoken;
									break;
	    							case 14:
									$bytes=$stringtoken;
									break;
							}
						}
	
						$workport=0;
						if ( $laddrport < 1024 ) { $workport = $laddrport; 
						} else {
							if ( $faddrport < 1024 ) { $workport = $faddrport; }
						}
						if ( $workport == 0 ) { $workport = $laddrport; }
						/* Time to Add Protocols Up */
						$counted=0;
						if ( $protocol == "TCP" ) {
							switch ($workport) {
								case 20:
								case 21:
									$ftpcount++;
									$ftp = $ftp + $bytes;
									$counted=1;
									break;
								case 22:
									$sshcount++;
									$ssh = $ssh + $bytes;
									$counted=1;
									break;
								case 23:
									$telnetcount++;
									$telnet = $telnet + $bytes;
									$counted=1;
									break;
								case 25:
									$smtpcount++;
									$smtp = $smtp + $bytes;
									$counted=1;
									break;
								case 53:
									$dnstcpcount++;
									$dnstcp = $dnstcp + $bytes;
									$counted=1;
									break;
								case 70:
									$gophercount++;
									$gopher = $gopher + $bytes;
									$counted=1;
									$break;
								case 80:
									$httpcount++;
									$http = $http + $bytes;
									$counted=1;
									break;
								case 110:
									$pop3count++;
									$pop3 = $pop3 + $bytes;
									$counted=1;
									break;
								case 119:
									$nntpcount++;
									$nntp = $nntp + $bytes;
									$counted=1;
									break;
								case 135:
									$locservtcpcount++;
									$locservtcp = $locservtcp + $bytes;
									$counted=1;
									break;
								case 137:
									$netbiosnstcpcount++;
									$netbiosnstcp = $netbiosnstcp + $bytes;
									$counted=1;
									break;
								case 138:
									$netbiosdgmtcpcount++;
									$netbiosdgmtcp = $netbiosdgmtcp + $bytes;
									$counted=1;
									break;
								case 139:
									$netbiosssntcpcount++;
									$netbiosssntcp = $netbiosssntcp + $bytes;
									$counted=1;
									break;
								case 143:
									$imapcount++;
									$imap = $imap + $bytes;
									$counted=1;
									break;
								case 443:
									$httpscount++;
									$https = $https + $bytes;
									$counted=1;
									break;
								case 465:
									$smtpscount++;
									$smtps = $smtps + $bytes;
									$counted=1;
									break;
								case 993:
									$imapscount++;
									$imaps = $imaps + $bytes;
									$counts=1;
									break;
								case 995:
									$pop3scount++;
									$pop3s = $pop3s + $bytes;
									$counted=1;
									break;
								default:
									$counted=1;
									$othertcpcount++;
									$othertcp = $othertcp + $bytes ;
									break;
							}
						}
						if ( $protocol == "UDP" ) {
							switch ($workport) {
								case 53:
									$dnsudpcount++;
									$dnsudp = $dnsudp + $bytes;
									$counted=1;
									break;
								case 69:
									$tftpcount++;
									$tftp = $tftp + $bytes;
									$counted=1;
									break;
								case 135:
									$locservudpcount++;
									$locservudp = $locservudp + $bytes;
									$counted=1;
									break;
								case 137:
									$netbiosnsudpcount++;
									$netbiosnsudp = $netbiosnsudp + $bytes;
									$counted=1;
									break;
								case 138:
									$netbiosdgmudpcount++;
									$netbiosdgmudp = $netbiosdgmudp + $bytes;
									$counted=1;
									break;
								case 139:
									$netbiosssnudpcount++;
									$netbiosssnudp = $netbiosssnudp + $bytes;
									$counted=1;
									break;
								case 161:
									$snmpcount++;
									$snmp = $snmp + $bytes;
									$counted=1;
									break;
								case 162:
									$snmptrapcount++;
									$snmptrap = $snmptrap + $bytes;
									$counted=1;
									break;
								default:
									$counted=1;
									$otherudpcount++;
									$otherudp = $otherudp + $bytes ;
									break;
							}
						}
						if ( ! $counted ) {
							$othercount++;
							$other = $other + $bytes;
						}
					}
				}
			}
		}
		if ( $SQLNumRows > 0 ) {
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage()."<BR>\n");
		}
	}
		startbody();
		echo $HeaderText;
		echo "<fort size=+1><B>Report Type:  " . reporttypename($reporttype) . "</B></FONT><BR><BR>\n";
	
		echo "<fort size=+1><B>Report Timeframe:  $date1 $time1 to $date2 $time2</B></FONT><BR>\n";
	echo "<fort size=+1><B>$goodrows rows valid in data set of $totalrows.</B></FONT><BR><BR>\n";

	echo "<TABLE COLS=8 BORDER=1 ><TR><TD><B>Protocol</b></TD><TD><B>TCP/UDP/Other</B></TD><TD><B># of Connections</B></TD><TD><B>Bytes TX'd/RX'd</B></TD>" .
		"<TD><B>Protocol</b></TD><TD><B>TCP/UDP/Other</B></TD><TD><B># of Connections</B></TD><TD><B>Bytes TX'd/RX'd</B></TD></TR>\n";
	echo "<TR align=center><TD>FTP</TD><TD>TCP</TD><TD>$ftpcount</TD><TD>$ftp</TD>";
	echo "<TD>SSH</TD><TD>TCP</TD><TD>$sshcount</TD><TD>$ssh</TD></TR>\n";
	
	echo "<TR align=center><TD>Telnet</TD><TD>TCP</TD><TD>$telnetcount</TD><TD>$telnet</TD>";
	echo "<TD>TFTP</TD><TD>UDP</TD><TD>$tftpcount</TD><TD>$tftp</TD></TR>\n";

	echo "<TR align=center><TD>HTTP</TD><TD>TCP</TD><TD>$httpcount</TD><TD>$http</TD>";
	echo "<TD>HTTPS</TD><TD>TCP</TD><TD>$httpscount</TD><TD>$https</TD></TR>\n";

	echo "<TR align=center><TD>Gopher</TD><TD>TCP</TD><TD>$gophercount</TD><TD>$gopher</TD>";
	echo "<TD>NNTP</TD><TD>TCP</TD><TD>$nntpcount</TD><TD>$nntp</TD></TR>\n";

	echo "<TR align=center><TD>SMTP</TD><TD>TCP</TD><TD>$smtpcount</TD><TD>$smtp</TD>";
	echo "<TD>SMTPS</TD><TD>TCP</TD><TD>$smtpscount</TD><TD>$smtps</TD></TR>\n";

	echo "<TR align=center><TD>POP3</TD><TD>TCP</TD><TD>$pop3count</TD><TD>$pop3</TD>";
	echo "<TD>POP3S</TD><TD>TCP</TD><TD>$pop3scount</TD><TD>$pop3s</TD></TR>\n";

	echo "<TR align=center><TD>IMAP</TD><TD>TCP</TD><TD>$imapcount</TD><TD>$imap</TD>";
	echo "<TD>IMAPS</TD><TD>TCP</TD><TD>$imapscount</TD><TD>$imaps</TD></TR>\n";

	echo "<TR align=center><TD>LocServe</TD><TD>TCP</TD><TD>$locservtcpcount</TD><TD>$locservtcp</TD>";
	echo "<TD>LocServe</TD><TD>UDP</TD><TD>$locservudpcount</TD><TD>$locservudp</TD></TR>\n";

	echo "<TR align=center><TD>Netbios-NS</TD><TD>TCP</TD><TD>$netbiosnstcpcount</TD><TD>$netbiosnstcp</TD>";
	echo "<TD>Netbios-NS</TD><TD>UDP</TD><TD>$netbiosnsudpcount</TD><TD>$netbiosnsudp</TD></TR>\n";

	echo "<TR align=center><TD>Netbios-DGM</TD><TD>TCP</TD><TD>$netbiosdgmtcpcount</TD><TD>$netbiosdgmtcp</TD>";
	echo "<TD>Netbios-DGM</TD><TD>UDP</TD><TD>$netbiosdgmudpcount</TD><TD>$netbiosdgmudp</TD></TR>\n";

	echo "<TR align=center><TD>Netbios-SSN</TD><TD>TCP</TD><TD>$netbiosssntcpcount</TD><TD>$netbiosssntcp</TD>";
	echo "<TD>Netbios-SSN</TD><TD>UDP</TD><TD>$netbiosssnudpcount</TD><TD>$netbiosssnudp</TD></TR>\n";

	echo "<TR align=center><TD>DNS</TD><TD>TCP</TD><TD>$dnstcpcount</TD><TD>$dnstcp</TD>";
	echo "<TD>DNS</TD><TD>UDP</TD><TD>$dnsudpcount</TD><TD>$dnsudp</TD></TR>\n";

	echo "<TR align=center><TD>SNMP</TD><TD>UDP</TD><TD>$snmpcount</TD><TD>$snmptrap</TD>";
	echo "<TD>SNMP Trap</TD><TD>UDP</TD><TD>$snmptrapcount</TD><TD>$snmptrap</TD></TR>\n";

	echo "<TR align=center><TD>Other</TD><TD>TCP</TD><TD>$othertcpcount</TD><TD>$othertcp</TD>";
	echo "<TD>Other</TD><TD>UDP</TD><TD>$otherudpcount</TD><TD>$otherudp</TD></TR>\n";

	echo "<TR align=center><TD>Other Protocols</TD><TD>Other</TD><TD>$othercount</TD><TD>$other</TD>";
	echo "<TD></TD><TD></TD><TD></TD><TD></TD></TR>\n";

	echo "</TABLE>";

	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	echo $FooterText;
%>
	</BODY>
</HTML>
<%
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
%>
