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

	$PageTitle="Saved Messages";

	if ( $group < 2 ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}
	do_header($PageTitle, 'viewsaves');

	echo $HeaderText;

	if ( idexist($dbsocket,"Syslog_TSave","TSave_ID",$saveid) ) {
		$SQLQuery = "select TSaveData_Date,TSaveData_Time,TSaveData_Host,TSaveData_Message,TSaveData_Facility,TSaveData_Severity from" . 
			" Syslog_TSaveData where TSave_ID=$saveid"; 
		$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
			die(pg_errormessage()."<BR>\n");
		$SQLNumRows = pg_numrows($SQLQueryResults);
		echo "<BR>Saved Log Description:  " . stripslashes(pgdatatrim(relatedata ($dbsocket,"Syslog_TSave","TSave_Desc","TSave_ID=$saveid"))) . "<BR><BR>\n";
		echo "Expires:  " . stripslashes(pgdatatrim(relatedata ($dbsocket,"Syslog_TSave","TSave_ExpireDate","TSave_ID=$saveid"))) . "<BR><BR>\n";
		openform("viewsaves.php","post",2,1,0);
		if ( $SQLNumRows > 0 ) {
			$deliverymessage="";
			if ( ( $emailaddress != "" ) && ( $action == "EMail Results" ) ) {
				$deliverymessage="Report from $REMOTE_USER\n\n";
			}
			echo "<TABLE BORDER=2>\n";
			echo "<TR><TD>Date</TD><TD>Time</TD><TD>Facility</TD><TD>Severity</TD><TD>Host</TD><TD>Message</TD></TR>\n";
			for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$date=stripslashes(pgdatatrim($SQLQueryResultsObject->tsavedata_date));
				$time=stripslashes(pgdatatrim($SQLQueryResultsObject->tsavedata_time));
				$host=stripslashes(pgdatatrim($SQLQueryResultsObject->tsavedata_host));
				$message=stripslashes(pgdatatrim($SQLQueryResultsObject->tsavedata_message));
				$sev=stripslashes(pgdatatrim($SQLQueryResultsObject->tsavedata_severity));
				$severity=verboseseverity(stripslashes(pgdatatrim($SQLQueryResultsObject->tsavedata_severity)));
				$facility=verbosefacility(stripslashes(pgdatatrim($SQLQueryResultsObject->tsavedata_facility)));
				if ( ( $sev == 4 ) || ( $sev == 3 ) ) { $fontcolor='#FF8800'; }
				if ( $sev <= 2 ) { $fontcolor='#FF0000'; }

				echo "<TR><TD>$date</TD><TD>$time</TD><TD>$facility</TD><TD><FONT COLOR=$fontcolor>$severity</font></TD><TD>$host</TD><TD NOWRAP>$message</TD></tr>\n";
				if ( ( $emailaddress != "" ) && ( $action == "EMail Results" ) ) {
					$deliverymessage=$deliverymessage . "$date $time $host $facility $severity $message\r\n";
				}
			}
		}
		echo "</TABLE>\n";
		if ( ( $emailaddress != "" ) && ( $action == "EMail Results" ) ) {
			$results=mail($emailaddress,"SMT EMail",$deliverymessage);
			if ( $results ) { echo "Email sent to SMTP server<BR>\n"; }
		}
		echo "Email Address:  ";
		formfield("emailaddress","text",3,1,0,40,128);
		formfield("saveid","hidden",3,1,0,40,40,$saveid);
		formsubmit("EMail Results",3,1,1);
	}

	pg_freeresult($SQLQueryResults) or
		die(pg_errormessage() . "<BR>\n");
	closeform();
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
