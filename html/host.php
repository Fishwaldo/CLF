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

	if ( ( $action == "Save" ) && ( $rensyslogs ) && ( ( pgdatatrim($host) != pgdatatrim($oldhost) ) && ( strlen(pgdatatrim($host)) > 0 ) ) ) {
		if ( $rensyslogs ) {
			renamehosts($dbsocket,"TSyslog","host='$oldhost'","host",$host);
			renamehosts($dbsocket,"Syslog_TArchive","host='$oldhost'","host",$host);
		}	
	}

	if ( $action == "Delete" ) { 
		$hosttype=2; 
	}
	if ( $action == "Add" ) {
		$hostid="";
		unset($host);
	}

	if ( $group != 3 ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	}                                           
	if ( $alertexpire > $syslogexpire ) {
		$alertexpire=$syslogexpire;
	}

	if ( ( $alertexpire == 0 ) && ( $syslogexpire != 0 ) ) { 
		$alertexpire = $syslogexpire ; 
	}

	if ( ( $hostadd ) && ( $host != "" ) ) {
		addhost($dbsocket,$host,$syslogexpire,$alertexpire,$typeid,$hostrate);
		$hostid = stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_THost","THost_ID","THost_Host='$host'")));
		addhostprocess($dbsocket,$hostid);
	} 
	if ( ( $hostmod ) && ( isset($hostid) ) && ( $host != "" ) ) {
		updatehost($dbsocket,$hostid,$host,$syslogexpire,$alertexpire,$typeid,$hostrate);
	}

	$PageTitle="Syslog Management Tool";
	do_header($PageTitle, 'host');
	if ( isset($hostid) && ( $hostid > 0 ) ) {
		$host=gethost($dbsocket,$hostid);
		$syslogexpire=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_THost","THost_LogExpire","THost_ID=$hostid")));
		$alertexpire=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_THost","THost_AlertExpire","THost_ID=$hostid")));
		$typeid=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_THost","TPremadeType_ID","THost_ID=$hostid")));
		$hostrate=stripslashes(pgdatatrim(relatedata($dbsocket,"Syslog_THost","THost_Rate","THost_ID=$hostid")));
		if ( $hostid == 0 ) {
			dbdisconnect($sec_dbsocket);
			dbdisconnect($dbsocket);
			exit;
	        }
	} else {
		$host="";
	}

	echo $HeaderText;
	if ( $hosttype != 2 ) {
		openform("host.php","post",2,1,0);
		if ( $hostid > 0 ) {
			formfield("hostid","Hidden",3,1,0,10,10,$hostid);
			formfield("hostmod","Hidden",3,1,0,10,10,"1");
			formfield("oldhost","Hidden",3,1,0,10,10,$host);
		} else {
			formfield("hostadd","Hidden",3,1,0,10,10,"1");
		}
		formfield("hosttype","Hidden",3,1,0,10,10,$hosttype);	
		echo "Host name:  ";
		formfield("host","text",3,1,1,40,128,$host);	
		echo "Expire Syslogs:  ";
		expiredropdown("syslogexpire",2,0,0,1,$syslogexpire); 
		echo "Expire Alerts:  ";
		expiredropdown("alertexpire",2,1,1,1,$alertexpire); 
		echo "Host Type:  ";
		premadetypedropdown ($dbsocket, "typeid",0,1,1,1,$typeid);		
		echo "Log Rate Warning Threshold:  ";
		logratesthreshold("hostrate",2,1,1,1,$hostrate);	
		if ( strval($hostid) > 0 ) { 
			echo "<input type='checkbox' name='rensyslogs' value='1'>Rename Syslogs<BR>\n";
		}
		formsubmit("Save",3,1,0);
		formreset("Reset",3,1,0);
		closeform();
	} else {
		if ( $confirmdelete ) { 
			if ( $delsyslogs ) { 
				/* Remove any alerts in the system that are tied to the host */
				drophostalerts($dbsocket,$hostid);

				/* Remove any syslogs in the TSyslog table */
				drophostsyslogs($dbsocket,$hostid); 

				/* Remove any syslogs in the archive table */
				drophostarchivesyslogs($dbsocket,$hostid);
			}
			drophostprocess($dbsocket,$hostid);
			dropprocessorhostfromprofile($dbsocket,$hostid);
			$delresults=drophostid($dbsocket,$hostid);
			if ( $delresults ) {
				echo "Delete Successfull<BR>\n";
			} else {
				echo "Delete Failed!<BR>\n";
			} 
		} else {
			openform("host.php","post",2,1,0);
			formfield("hostid","Hidden",3,1,0,10,10,$hostid);
			formfield("confirmdelete","Hidden",3,1,0,10,10,1);
			echo "Are you sure you wish to delete $host?<BR>\n";
			echo "<input type='checkbox' name='delsyslogs' value='1'>Delete Syslogs<BR>\n";
			formsubmit("Delete",3,1,0);
			closeform();
			openform("background.php","post",2,1,0);
			formsubmit("Do NOT delete",3,1,1);
			closeform();
		}
	}
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";
	do_footer();
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
php?>
