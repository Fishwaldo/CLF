#!/usr/bin/perl
#
#
use DBI;

$configfile = "/etc/log.d/db.conf";
eval('require("$configfile")');
die "*** Failed to eval() file $configfile:\n$@\n" if ($@);

if (!@ARGV) {
	print "Usage: storelog.pl system datafile [date]\n";
	exit (99);
}

#
# Open the logfiles we're writing to the database and put all data in 
# the $data variable.
#
open (DATA, $ARGV[1]);
while (<DATA>) {
	$data .= $_;
}
close (DATA);

if (@ARGV[2] && (@ARGV[2] ne "all") && (@ARGV[2] ne "yesterday") && (@ARGV[2] ne "today")) {
	$date = "'". @ARGV[2]. "'";
} elsif (@ARGV[2] eq "yesterday") {
	$date = "(CURRENT_DATE - 1)";
} else {
	$date = "CURRENT_DATE";
}

#
# Open the database connection
#
my $dbh = DBI->connect($DBI, $user, $password) or die DBI::errstr;

# Make sure that the data is properly escaped
my $qdata = $dbh->quote($data);

$sql = "insert into syslog_tsummary (host, date, data) values ('". @ARGV[0]. "', $date, $qdata)";

my $sth = $dbh->prepare($sql) 	or die "Can't prepare statement: $DBI::errstr";
my $rc = $sth->execute	or die "Can't execute statement: $DBI::errstr";

# check for problems which may have terminated the fetch early
die $sth->errstr if $sth->err;

$dbh->disconnect();
