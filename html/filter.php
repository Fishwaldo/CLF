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

	if ( $group < 1 ) {
		dbdisconnect($sec_dbsocket);
		dbdisconnect($dbsocket);
		exit;
	} 

	if ( ( $action == "Modify" ) && ( isset($newfilter) ) ) { $newfilter = ""; }

	if ( ( $action == "Save Filter Header" ) && ( strlen(pgdatatrim($filtertitle)) > 0 ) && 
             ( ( $userorglobal == 1 ) || ( $userorglobal == 2 ) ) ) { 
		if ( $group < 2 ) { $userorglobal=1; }
		if ( isset($filterid) ) {
			updatefilter($dbsocket,$filterid,$filtertitle,$userorglobal) ; 
		} else {
			addfilterheader($dbsocket,$userorglobal,$filtertitle,$REMOTE_ID) ;
			$filterid=relatedata ($dbsocket,"Syslog_TFilter","TFilter_ID","TFilter_Desc='$filtertitle'");
		}
	}

	if ( ( $filtermain != "1" ) || ( ( $filtermain == "1" ) && ( $action != "Add" ) ) ) {
		if ( isset($filterid) && $filterid >= 1 ) {
			$filterowner=relatedata ($dbsocket,"Syslog_TFilter","TLogin_ID","TFilter_ID=$filterid");
		}
		if ( isset($filterdataid) && $filterdataid >= 1 ) {
			$filterdataowner=relatedata ($dbsocket,"Syslog_TFilter,Syslog_TFilterData","TLogin_ID","Syslog_TFilter.TFilter_ID=Syslog_TFilterData.TFilter_ID and Syslog_TFilterData.TFilterData_ID=$filterdataid");
		} 
		if ( ( $action != "Delete User Filters" ) && ( ( isset($filterowner) && ($filterowner != $REMOTE_ID )) || ( ( isset($filterdataowner) && ($filterdataowner != $REMOTE_ID) ) && ( $filterdataid >= 1 ) && ( isset($filterdataid) ) ) ) ) {
			dbdisconnect($sec_dbsocket);
			dbdisconnect($dbsocket);
			exit;
		}                                           

		if ( isset($filterid) ) {
			$userorglobal=relatedata ($dbsocket,"Syslog_TFilter","TFilter_UserOrGlobal","TFilter_ID=$filterid");
			$filtertitle=relatedata ($dbsocket,"Syslog_TFilter","TFilter_Desc","TFilter_ID=$filterid");
		}

		$deletestatus="FAILED";
		if ( $action == "Delete" ) {
			if (!isset($filtermod) || (isset($filtermod) && ($filtermod != 1)) ) {
				if ( ( dropallfilterdata($dbsocket,$filterid) ) && ( dropfilter($dbsocket,$filterid) ) ) { $deletestatus="Success"; }
			} else {
				if ( dropfilterdata($dbsocket,$filterdataid) ) { $deletestatus="Success"; } 
			}
		} 
		if ( ( $group >= 3 ) && ( $action == "Delete User Filters" ) ) {
			$SQLQuery="begin;delete from syslog_tfilterdata where syslog_tfilterdata.tfilter_id=syslog_tfilter.tfilter_id and syslog_tfilter.tlogin_id=$userid; delete from syslog_tfilter where syslog_tfilter.tlogin_id=$userid;commit;";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			if ( $SQLQueryResults ) { $deletestatus="Success"; }
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	
		if ( isset($filteradd) ) {
			if ( $startfacility > $stopfacility ) {
				$temp=$startfacility;
				$startfacility=$stopfacility;
				$stopfacility=$temp;
			}
			if ( $startseverity > $stopseverity ) {
				$temp=$startseverity;
				$startseverity=$stopseverity;
				$stopseverity=$temp;
			}

			if ( ( strlen($filter) > 0 ) || ( $filterorlevel == 3 ) ) { addfilter($dbsocket,$filter,$filterid,$include,$filterorlevel,$startfacility,$stopfacility,$startseverity,$stopseverity); }
		}

		if ( ( $action == "Save" ) && ( $filtermod ) && ( strval($filterdataid) > 0 ) ) {
			if ( $startfacility > $stopfacility ) {
				$temp=$startfacility;
				$startfacility=$stopfacility;
				$stopfacility=$temp;
			}
			if ( $startseverity > $stopseverity ) {
				$temp=$startseverity;
				$startseverity=$stopseverity;
				$stopseverity=$temp;
			}
			updatefilterdata($dbsocket,$filterdataid,$filter,$include,$filterorlevel,$startfacility,$stopfacility,$startseverity,$stopseverity) ;
		}

		if ( ( $deletestatus == "FAILED" ) || ( ( $deletestatus == "Success" ) && ( $action == "Delete" ) && ( ! isset($filtermain) ) ) ) { 
			$SQLQuery="select * from Syslog_TFilterData where TFilter_ID='$filterid' order by TFilterData_ID";
			$SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
				die(pg_errormessage()."<BR>\n");
			$SQLNumRows = pg_numrows($SQLQueryResults);
		}
	} else {
		$SQLNumRows = 0;
	}
	$PageTitle="Syslog Management Tool";
	do_header($PageTitle, 'filter');

	if ( ( ( $group >= 3 ) && ( $action == "Delete User Filters" ) ) || ( ( $action == "Delete" ) && ((isset($filterdataid) && $filterdataid < 1 ) ) ) ) { 
		echo "<BR>Delete:  $deletestatus<BR>\n";
	} else {
		echo "<TABLE COLS=4 BORDER=1>\n";
		echo "<TR><TD>";
		openform("filter.php","post",2,1,0);
		if ( ( $filtermain ) && ( $action == "Add" ) ) { 
			formfield("newfilter","Hidden",3,1,0,10,10,1);
		} else {
			formfield("filterid","Hidden",3,1,0,10,10,$filterid);
		}
		echo "Filter Description:  ";
		if (! isset($filtertitle)) {
			$filtertitle = '';
		}
		formfield("filtertitle","text",3,1,1,40,128,$filtertitle);
		echo "</TR>";
		if ( $group >= 2 ) {
			if ( isset($userorglobal) && ($userorglobal == 1) ) {
				echo "<TR><TD><input type=radio name=userorglobal value=1 checked>Private  ";
				echo "<input type=radio name=userorglobal value=2>Global</TD></TR>";
			} else {
				echo "<TR><TD><input type=radio name=userorglobal value=1>Private  ";
				echo "<input type=radio name=userorglobal value=2 checked>Global</TD></TR>";
			}
		} else {
			formfield("userorglobal","hidden",3,1,1,40,40,1);
		}
		echo "<TR><TD>";
		formsubmit("Save Filter Header",3,1,0);
		echo "</TD></TR>";
		closeform();
		echo "</TABLE><BR>\n";
		if ( ( ( isset($filterid) && ($filterid > 0) ) && ( $filtermain != 1 ) ) || ( ( $filtermain == 1 ) && ( $action != "Add" ) ) ) {
			echo "<U><B>New Entry:</B></U><BR>\n";
			echo "<TABLE COLS=4 BORDER=1>\n";
			echo "<TR><TD width=115>";
			openform("filter.php","post",2,1,0);
			formsubmit("Add",3,1,0);
			formfield("filterid","Hidden",3,1,0,10,10,$filterid);
			formfield("filteradd","Hidden",3,1,0,10,10,"1");
			echo "</TD><TD width=90>";
			echo "<input type=radio name=include value=1 checked>Include</TD><TD width=90>";
			echo "<input type=radio name=include value=0>Exclude</TD>";
			echo "<TD>Filter:  ";
			formfield("filter","text",3,1,1,40,128,"");
			echo "</TD></TR><TR><TD COLSPAN=4>";
        		echo "Filter Type:  <input type=radio name=filterorlevel value=1 checked>Expression  ";
        		echo "<input type=radio name=filterorlevel value=3>Facility & Severity  ";
        		echo "<input type=radio name=filterorlevel value=2>Expression w/ Facility & Severity</TD></TR><TR><TD COLSPAN=3>";

			echo "Facility Range:  ";
			facilitydropdown("startfacility",1,0,0,1,0);
			echo " to ";
			facilitydropdown("stopfacility",1,0,0,1,23);
			echo "</TD><TD>Severity Range:  ";
			severitydropdown("startseverity",1,0,0,1,0);
			echo " to ";
			severitydropdown("stopseverity",1,0,0,1,7);
			closeform();
			echo "</TD></TR></TABLE><BR>\n";
		}
		if ( $SQLNumRows > 0 ) {
			echo "<TABLE COLS=4 BORDER=1>\n";
			for ( $loop = 0 ; $loop != $SQLNumRows ; $loop++ ) {
				echo "<TR><TD width=50>";
				openform("filter.php","post",2,1,0);

				$SQLQueryResultsObject = pg_fetch_object($SQLQueryResults,$loop) or
					die(pg_errormessage()."<BR>\n");
				$filterdataid=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_id));
				echo "Filter ID:  $filterdataid</TD></TR><TR><TD WIDTH=115>";
				formsubmit("Save",3,1,0);
				formsubmit("Delete",3,1,0);
				$filter=pgdatatrim($SQLQueryResultsObject->tfilterdata_filter);
				$include=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_include));
				$filterorlevel=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_filterorlevel));
				$startfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_startfacility));
				$stopfacility=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_stopfacility));
				$startseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_startseverity));
				$stopseverity=stripslashes(pgdatatrim($SQLQueryResultsObject->tfilterdata_stopseverity));
				formfield("filterid","Hidden",3,1,0,10,10,$filterid);
				formfield("filterdataid","Hidden",3,1,0,10,10,$filterdataid);
				formfield("filtermod","Hidden",3,1,0,10,10,"1");
				echo "</TD><TD width=90>";
				if ( $include ) {
					echo "<input type=radio name=include value=1 checked>Include</TD><TD width=90>";
					echo "<input type=radio name=include value=0>Exclude</TD>";
				} else {
					echo "<input type=radio name=include value=1>Include</TD><TD width=90>";
					echo "<input type=radio name=include value=0 checked>Exclude</TD>";
				}
				echo "<TD>Filter:  ";
				formfield("filter","text",3,1,1,40,128,$filter);	
				echo "</TD></TR><TR><TD COLSPAN=4>";
				echo "Rule Type:  <input type=radio name=filterorlevel value=1 ";
				if ( ( $filterorlevel != "2" ) && ( $filterorlevel != "3" ) ) { $filterorlevel=1;}
				if ( $filterorlevel == 1 ) { echo " checked "; }
				echo ">Expression  ";
				echo "<input type=radio name=filterorlevel value=3";
				if ( $filterorlevel == 3 ) { echo " checked "; }
				echo ">Facility & Severity  ";
				echo "<input type=radio name=filterorlevel value=2";
				if ( $filterorlevel == 2 ) { echo " checked "; }
				echo ">Expression w/ Facility & Severity";
				echo "</TD></TR><TR><TD COLSPAN=3>";
				echo "Facility Range:  ";
				facilitydropdown("startfacility",1,0,0,1,$startfacility);
				echo " to ";
				facilitydropdown("stopfacility",1,1,1,1,$stopfacility);
				echo "</TD><TD>Severity Range:  ";
				severitydropdown("startseverity",1,0,0,1,$startseverity);
				echo " to ";
				severitydropdown("stopseverity",1,1,1,1,$stopseverity);
				echo "</TD></TR><TR><TD COLSPAN=4></TD></TR>";

				closeform();
			}
			echo "</TABLE>\n";
		}
		if ( $SQLNumRows > 0 ) {
			pg_freeresult($SQLQueryResults) or
				die(pg_errormessage() . "<BR>\n");
		}
	}
	$endtime=time();
	echo "<BR>Page loaded in " . ($endtime - $begintime) . " seconds.<BR>\n";

	do_footer();
?>
	</BODY>
</HTML>
<?php
	dbdisconnect($sec_dbsocket);
	dbdisconnect($dbsocket);
?>
