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

/********************************************************************/
/*                                                                  */
/*  File:  pgsql.php                                                */
/*  Purpose:  Provide a slimmed down interface to interact with     */
/*            PGSQL.  Also used to abstract usernames/passwords     */
/*            by providing extra protection                         */
/*                                                                  */
/********************************************************************/

/********************************************************************/
/*                                                                  */
/* Function:  dbconnect                                             */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A streamlined function used to connect to PGSQL    */
/*                                                                  */
/********************************************************************/
function dbconnect($dbname,$user,$passwd) {

        $host = "127.0.0.1";
        $dbsocket = pg_connect("host=$host dbname=$dbname user=$user password=$passwd") or
                die(pg_errormessage()."<BR>\n");
        return($dbsocket);
}

/********************************************************************/
/*                                                                  */
/* Function:  dbdisconnect                                          */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  A streamlined function used to disconnect from     */
/*               PGSQL                                              */
/*                                                                  */
/********************************************************************/
function dbdisconnect($dbsocket) {

        pg_close($dbsocket) or
                die(pg_errormessage()."<BR>\n");
}

/********************************************************************/
/*                                                                  */
/* Function:  pgdatatrim(deprecated)                                */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Used to trim out trailing spaces for PGSQL char    */
/*               variables                                          */
/*                                                                  */
/********************************************************************/
function pgdatatrim($string) {

        $Results=ltrim(rtrim($string));
        return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  relatedata                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  Database/table generic function to relate simple   */
/*               queries and return the first row only              */
/*                                                                  */
/********************************************************************/
function relatedata ($dbsocket,$tablename,$field,$condition) {

        $SQLQuery="select $field from $tablename where $condition";
        $SQLQueryResults = pg_exec($dbsocket,$SQLQuery) or
                die(pg_errormessage() . "<BR>\n");
        $SQLNumRows = pg_numrows($SQLQueryResults);
        if ( $SQLNumRows > 0 ) {
                $SQLQueryResultsArray=pg_fetch_array($SQLQueryResults) or
                        die(pg_errormessage() . "<BR>\n");
                $Results = $SQLQueryResultsArray[0];
        } else {
		$Results = '';
	}
        pg_freeresult($SQLQueryResults) or
                die(pg_errormessage() . "<BR>\n");
        return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  embededsql                                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  check for embeded SQL keywords, if found, return 1 */
/*                                                                  */
/********************************************************************/
function embededsql($var) {

	$pgsql_commands=array('ABORT','ALTER GROUP','ALTER TABLE','ALTER USER','BEGIN','CHECKPOINT','CLOSE',
			'CLUSTER','COMMENT','COMMIT','COPY','CREATE AGGREGATE','CREATE CONSTRAINT TRIGGER',
			'CREATE DATABASE','CREATE FUNCTION','CREATE GROUP','CREATE INDEX','CREATE LANGUAGE',
			'CREATE OPERATOR','CREATE RULE','CREATE SEQUENCE','CREATE TABLE','CREATE TABLE AS',
			'CREATE TRIGGER','CREATE TYPE','CREATE USER','CREATE VIEW','DECLARE','DELETE',
			'DROP AGGREGATE','DROP DATABASE','DROP FUNCTION','DROP GROUP','DROP INDEX','DROP LANGUAGE',
			'DROP OPERATOR','DROP RULE','DROP SEQUENCE','DROP TABLE','DROP TRIGGER','DROP TYPE',
			'DROP USER','DROP VIEW','EXPLAIN','FETCH','GRANT','INSERT','LISTEN','LOAD','LOCK',
			'MOVE','NOTIFY','REINDEX','RESET','REVOKE','ROLLBACK','SELECT','SELECT INTO',
			'SET CONSTRAINTS','SET TRANSACTION','SHOW','TRUNCATE','UNLISTEN','UPDATE','VACUUM');
	$testvar=strtoupper($var);
	$Results=0;
	for ( $loop = 0 ; $loop != (count($pgsql_commands)-1) ; $loop++ ) {
		if ( substr_count($testvar,$pgsql_commands[$loop]) ) { $Results=1; } 
	}
	return($Results);
}

/********************************************************************/
/*                                                                  */
/* Function:  fixappostrophe(deprecated)                            */
/* Stability(1 low - 5 high):  5                                    */
/* Description:  convert single "'"s to "''"s for SQL statements    */
/*                                                                  */
/********************************************************************/
function fixappostrophe($string) {

        $Results="";
        for ( $loop = 0; $loop != strlen($string) ; $loop ++ ) {
                if ( substr($string,$loop,1) == "'" ) {
                        $Results=$Results . "''";
                } else {
                        $Results=$Results . substr($string,$loop,1);
                }
        }
        return($Results);
}

%>

