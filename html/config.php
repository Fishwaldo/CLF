<?php

#How many messages to process in one go with processlogs.php
$SQLLIMIT = 500000;
#Specify a file that locks the processors
$lockfile = "/tmp/processor.lock";
#How long in hours before we send warnings that a processor is locked.
$locktime = 1;
#path to the library files.
$libpath = '/var/www/lib';
#path to the archive directory (Currently in-active)
$archivedir = '/var/www/html/Archives';
#path to the logwatch config files
$logwatchreports = '/etc/log.d/configs/';



#No need to edit anything else
require_once($libpath.'/pgsql.php');
require_once($libpath.'/generalweb.php');
require_once($libpath.'/secframe.php');
require_once($libpath.'/pix.php');

require_once('header.php');
?>