#!/usr/bin/perl
#
#
use DBI;

$configfile = "/etc/log.d/db.conf";
eval('require("$configfile")');
die "*** Failed to eval() file $configfile:\n$@\n" if ($@);

if (!@ARGV) {
	print "Usage: dumplog.pl system [all|today|yesterday|yyyy-mm-dd]\n";
	exit (99);
}

if ( ! -d $temp_log_dir ) {
	print "$temp_log_dir not a directory\n";
	exit (99);
}

#
# Open the logfiles we're writing to later
#
open (MESSAGES_LOG, ">$temp_log_dir/messages");

#
# Open the database connection
#
my $dbh = DBI->connect($DBI, $user, $password) or die DBI::errstr;

#
# Open the database connection
#
if (@ARGV[1] && @ARGV[1] ne "all") {
	if (@ARGV[1] eq "today") {
		$date = "and date=CURRENT_DATE";
	} elsif (@ARGV[1] eq "yesterday") {
		$date = "and date=(CURRENT_DATE - 1)";
	} else {
		$date = "and date='". @ARGV[1]. "'";
	}
}

$sql = "select to_char(date, 'Mon DD') as date, time, host, message from syslog_tarchive 
	where host='". @ARGV[0]. "' $date order by date, time";
my $sth = $dbh->prepare($sql) 	or die "Can't prepare statement: $DBI::errstr";
my $rc = $sth->execute			or die "Can't execute statement: $DBI::errstr";

if (!$sth->rows) {
	print "Error: no matching data\n";
	exit (99);
}

while (($date,$time,$host,$message) = $sth->fetchrow_array) {
	$log_string = "$date $time $host $message\n";
	print MESSAGES_LOG $log_string;
}

close (MESSAGES_LOG);

# check for problems which may have terminated the fetch early
die $sth->errstr if $sth->err;

$dbh->disconnect();
