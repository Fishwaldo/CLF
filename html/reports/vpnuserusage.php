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
	if ( $SERVER_PORT < 443 ) {
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

	if ( ! $datatype ) { $datatype = 1; }
	if ( ( $group < 2 ) && ( $datatype > 3 ) ) { $datatype = 1; }

        $time1=$hour . ":" . $minute . ":00";
        $date1=$month . "-" . $day . "-" . $year;
        $date2=$month2 . "-" . $day2 . "-" . $year2;
        $time2=$hour2 . ":" . $minute2 . ":59";

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

	$SQLQuery="select host,date,time,message,tsyslog_id from Syslog_TArchive "; 
	if ( $datatype == 1 ) {
		$host=gethost($dbsocket,$hostid);
		$SQLQuery = $SQLQuery . " where host='$host' and ";
	}
	if ( $datatype == 2 ) {
		$SQLQuery = $SQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where Syslog_TArchive.host=Syslog_THost.THost_Host and TPremadeType_ID=$typeid and ";
	}
	if ( $datatype == 3 ) {
		$SQLQuery = $SQLQuery . " ,Syslog_THost,Syslog_TProcessorProfile where Syslog_TCustomerProfile.TLogin_ID=$userid and Syslog_TArchive.host=Syslog_THost.THost_Host and Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID and ";
	}
	if ( $datatype == 4 ) {
		$SQLQuery = $SQLQuery . " ,Syslog_THost,Syslog_TCustomerProfile where " .
			"( Syslog_TCustomerProfile.TLogin_ID=$userid ) and " .
			"( Syslog_TArchive.host=Syslog_THost.THost_Host ) and " .
			"( Syslog_THost.THost_ID=Syslog_TCustomerProfile.THost_ID ) and " .
			"( Syslog_THost.TPremadeType_ID=$typeid ) and ";
	}

	if ( $date1 == $date2 ) {
		$SQLQueryDate="date = '$date1' and ( time >= '$time1' and time <= '$time2')";
	}

	if ( ( ( date("z",$timestamp2) - date("z",$timestamp) ) == 1 ) && ( $year == $year2 ) ) {
		echo "HI<BR>\n";
		$SQLQueryDate="( ( date = '$date1' and time >= '$time1' ) or " .
			"( date = '$date2' and time <= '$time2' ) ) ";
	}
	if ( ( date("z",$timestamp2) - date("z",$timestamp) ) > 1 ) {
		$SQLQueryDate=" ( ( date = '$date1' and time >= '$time1' ) or " .
			"( date > '$date1' and date < '$date2' ) or " .
			"( date = '$date2' and time <= '$time2' ) )";
	}
	$SQLQuery = $SQLQuery . $SQLQueryDate . " order by date, time, TSyslog_ID";

	if ( $timestamp <= $timestamp2 ) {
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
	} else {
		$SQLNumRows = 0;
	}

%>
<HTML>
	<HEAD>
		<TITLE>
<%	echo $PageTitle; %>
		</TITLE>
	</HEAD>
<%
	startbody();
	echo $HeaderText;
	echo "<fort size=+1><B>Report Type:  " . reporttypename($reporttype) . "</B></FONT><BR><BR>\n";

	echo "<fort size=+1><B>Report Timeframe:  $date1 $time1 to $date2 $time2</B></FONT><BR>\n";
	
	if ( $SQLNumRows != 0 ) {
		echo "<TABLE COLS=9 BORDER=1><TR><TD ALIGN=CENTER><B>Disconnect Date & Time</b></TD><TD ALIGN=CENTER><B>VPN Device</B></TD><TD ALIGN=CENTER><B>User</B></TD><TD ALIGN=CENTER><B>Group</B></TD><TD ALIGN=CENTER><B>IP Address</B></TD><TD ALIGN=CENTER><B>Duration</B></TD><TD ALIGN=CENTER><B>TX Bytes</B></TD><TD ALIGN=CENTER><B>RX Bytes</b></TD><TD><B>Disconnect Reason</B></TD></TR>\n";
		for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
			$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
				die(pg_errormessage()."<BR>\n");
			$host=pgdatatrim($SQLQueryResultsObject->host);
			$message=pgdatatrim($SQLQueryResultsObject->message);
			$date=pgdatatrim($SQLQueryResultsObject->date);
			$time=pgdatatrim($SQLQueryResultsObject->time);
			if ( ( ereg("Bytes xmt",$message) ) && ( ereg("Bytes rcv",$message) ) ) {
				$break=0;
				$stringtoken = strtok($message," ");
				$stage=0;
				$ip=0;
				$user="";
				$ip=0;
				$duration=0;
				$group=0;
				$groupdesc="";
				$rx=-98132984712;
				$tx=0;
				$reason=0;
				/* Parse the message */
				$countarrayelements=count(split(" ",$message));
				/* echo "<TR><TD COLSPAN=8>message:  $message</TD></TR>\n"; */
				while ( $break != $countarrayelements ) {
					$break++;
					$token = strtok(" ");
					if ( ( $stage ) && ( ! $ip ) ) {
						$ip=$token;
					}
					if ( ( $stage ) && ( $user == 1 ) ) {
						$user=substr($token,1,strlen($token) -3 ) ;
					}
					if ( ( $stage ) && ( $group == 2 ) && ( $token != "disconnected:" ) ) {
						$groupdesc=$groupdesc . " $token";
					}
					if ( ( $stage ) && ( $group == 2 ) && ( $token == "disconnected:" ) ) {
						$group = 0;
					}
					if ( ( $stage ) && ( $group == 1 ) ) {
						$groupdesc=$token;
						$group=2;
					}
					if ( ( $stage ) && ( $duration == "1" ) ) {
						$duration=$token;
					}
					if ( ( $stage ) && ( $tx == 1 ) ) {
						$tx=$token;
					}
					if ( ( $stage ) && ( $token != "Reason:" ) && ( $reason != "0" ) ) {
						$reason = $reason . " " . $token;
					}
					if ( ( $stage ) && ( $rx == -98132984712 ) ) {
						$rx=$token;
						$reason="";
					}
					if ( $stage ) {
						if ( ( $token == "User" ) && ( strlen($user) <= 1 ) ) { $user=1; }	
						if ( $token == "Duration:" ) { $duration=1; }
						if ( $token == "Group" ) { $group=1; }
						if ( $token == "xmt:" ) { $tx=1; }
						if ( $token == "rcv:" ) { $rx=-98132984712; }
					}
					if ( substr($token,0,4) == "RPT=" ) {
						$stage=1;
					}
				}
				/* sanitize data based on different vpn software versions */

				/* Remove trailing ":" */
				if ( substr($ip,strlen($ip)-1,1) == ":" ) {
					$ip = substr($ip,0,strlen($ip)-1);
				}
				/* Remove [s */
				if ( substr($groupdesc,0,1) == "[" ) {
					$groupdesc = substr($groupdesc,1,strlen($groupdesc));
				}
				/* Remove ]s */
				if ( substr($groupdesc,strlen($groupdesc)-1,1) == "]" ) {
					$groupdesc = substr($groupdesc,0,strlen($groupdesc)-1);
				}
				echo "<TR><TD>$date $time</TD><TD ALIGN=CENTER>$host</TD><TD ALIGN=CENTER>$user</TD><TD ALIGN=CENTER>$groupdesc</TD><TD ALIGN=CENTER>$ip</TD><TD ALIGN=CENTER>$duration</TD><TD ALIGN=CENTER>$tx</TD><TD ALIGN=CENTER>$rx</TD><TD>$reason</TD></TR>\n";
			}
		}
		echo "</TABLE>";
	}
	if ( $SQLNumRows > 0 ) {
		pg_freeresult($SQLQueryResults) or
			die(pg_errormessage()."<BR>\n");
	}

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
