<?php

$SQLLIMIT = 500000;
$lockfile = "/tmp/processor.lock";
$locktime = 1;
$libpath = '/var/www/lib';
$archivedir = '/var/www/html/Archives';
$logwatchreports = '/etc/log.d/configs/';

require_once($libpath.'/pgsql.php');
require_once($libpath.'/generalweb.php');
require_once($libpath.'/secframe.php');
require_once($libpath.'/pix.php');

require_once('header.php');
?>