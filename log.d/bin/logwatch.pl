#!/usr/bin/perl -w
use strict;
##########################################################################
# $Id: logwatch.pl,v 1.111 2004/06/21 15:00:44 kirk Exp $
##########################################################################
# Most current version can always be found at:
# ftp://ftp.logwatch.org/pub/redhat/RPMS

########################################################
# This was written and is maintained by:
#    Kirk Bauer <kirk@kaybee.org>
#
# Please send all comments, suggestions, bug reports,
#    etc, to logwatch@logwatch.org.
#
########################################################

my $BaseDir = "/etc/log.d";
#my $BaseDir = "/home/kirk/cvs-work/logwatch";
my $ConfigDir = "$BaseDir/conf";

#Added to create switches for different os options -mgt
#For now working on Linux and SunOS
my $OSname = `uname -s`;
chomp $OSname;

my $Version = '5.2';
my $VDate = '06/21/04';

#############################################################################

use Getopt::Long;
my (%Config, @ServiceList, @LogFileList, %ServiceData, %LogFileData);
my (@AllShared, @AllLogFiles, @FileList);
# These need to not be global variables one day
my (@ReadConfigNames, @ReadConfigValues);

# Default config here...
$Config{'detail'} = 0;
$Config{'mailto'} = "root";
$Config{'save'} = "";
$Config{'print'} = 0;
$Config{'range'} = "yesterday";
$Config{'debug'} = 0;
$Config{'archives'} = 0;
$Config{'tmpdir'} = "/tmp";
$Config{'splithosts'} = 0;
$Config{'multiemail'} = 0;
# Logwatch now does some basic searching for logs
# So if the log file is not in the log path it will check /var/adm
# and then /var/log -mgt
$Config{'logdir'} = "/var/log";
chomp($Config{'hostname'} = `hostname`);

#############################################################################

sub Usage () {
   # Show usage for this program
   print "\nUsage: $0 [--detail <level>] [--logfile <name>]\n" . 
      "   [--print] [--mailto <addr>] [--archives] [--range <range>] [--debug <level>]\n" .
      "   [--save <filename>] [--help] [--version] [--service <name>]\n" .
      "   [--splithosts] [--multiemail]\n\n";
   print "--detail <level>: Report Detail Level - High, Med, Low or any #.\n";
   print "--logfile <name>: *Name of a logfile definition to report on.\n";
   print "--service <name>: *Name of a service definition to report on.\n";
   print "--print: Display report to stdout.\n";
   print "--mailto <addr>: Mail report to <addr>.\n";
   print "--archives: Use archived log files too.\n";
   print "--save <filename>: Save to <filename>.\n";
   print "--range <range>: Date range: Yesterday, Today or All.\n";
   print "--debug <level>: Debug Level - High, Med, Low or any #.\n";
   print "--splithosts: Create a report for each host in syslog.\n";
   print "--multiemail: Send each host report in a separate email.  Ignored if \n";
   print "              not using --splithosts.\n";
   print "--version: Displays current version.\n";
   print "--help: This message.\n";
   print "* = Switch can be specified multiple times...\n\n";
   exit (99);
}

my %wordsToInts = (yes  => 1,  no     => 0,
                   true => 1,  false  => 0,
                   on   => 1,  off    => 0,
                   high => 10,
                   med  => 5,  medium => 5,
                   low  => 0);

sub getInt {
   my $word = shift;
   my $tmpWord = lc $word;
   $tmpWord =~ s/\W//g;
   return $wordsToInts{$tmpWord} if (defined $wordsToInts{$tmpWord});
   unless ($word =~ s/^"(.*)"$/$1/) {
      return lc $word;
   }
   return $word;
}
              
sub CleanVars {
   foreach (keys %Config) {
      $Config{$_} = getInt($Config{$_});
   }
}

sub PrintStdArray (@) {
   my @ThisArray = @_;
   my $i;
   for ($i=0;$i<=$#ThisArray;$i++) {
      print "[" . $i . "] = " . $ThisArray[$i] . "\n";
   }
}

sub PrintConfig () {
   # for debugging, print out config...
   foreach (keys %Config) {
      print $_ . ' -> ' . $Config{$_} . "\n";
   }
   print "Service List:\n";
   PrintStdArray @ServiceList;
   print "\n";
   print "LogFile List:\n";
   PrintStdArray @LogFileList;
   print "\n\n";
}

# for debugging...
sub PrintServiceData () {
   my ($ThisKey1,$ThisKey2,$i);
   foreach $ThisKey1 (keys %ServiceData) {
      print "\nService Name: " . $ThisKey1 . "\n";
      foreach $ThisKey2 (keys %{$ServiceData{$ThisKey1}}) {
         next unless ($ThisKey2 =~ /^\d+-/);
         print "   $ThisKey2 = $ServiceData{$ThisKey1}{$ThisKey2}\n";
      }
      for ($i=0;$i<=$#{$ServiceData{$ThisKey1}{'logfiles'}};$i++) {
         print "   Logfile = " . $ServiceData{$ThisKey1}{'logfiles'}[$i] . "\n";
      }
   }
}

# for debugging...
sub PrintLogFileData () {
   my ($ThisKey1,$ThisKey2,$i);
   foreach $ThisKey1 (keys %LogFileData) {
      print "\nLogfile Name: " . $ThisKey1 . "\n";
      foreach $ThisKey2 (keys %{$LogFileData{$ThisKey1}}) {
         next unless ($ThisKey2 =~ /^\d+-/);
         print "   $ThisKey2 = $LogFileData{$ThisKey1}{$ThisKey2}\n";
      }
      for ($i=0;$i<=$#{$LogFileData{$ThisKey1}{'logfiles'}};$i++) {
         print "   Logfile = " . $LogFileData{$ThisKey1}{'logfiles'}[$i] . "\n";
      }
      for ($i=0;$i<=$#{$LogFileData{$ThisKey1}{'archives'}};$i++) {
         print "   Archive = " . $LogFileData{$ThisKey1}{'archives'}[$i] . "\n";
      }
   }
}

sub ReadConfigFile ($) {
   my $FileName = $_[0];
   @ReadConfigNames = ();
   @ReadConfigValues = ();
   if ($Config{'debug'} > 5) {
      print "ReadConfigFile: Opening " . $FileName . "\n";
   }
   open (READCONFFILE, $FileName) or die "Cannot open file $FileName: $!\n";
   while (my $line = <READCONFFILE>) {
      if ($Config{'debug'} > 9) {
         print "ReadConfigFile: Read Line: " . $line;
      }
      $line =~ s/#.*$//;
      next if ($line =~ /^\s*$/);

      my ($name, $value) = split /=/, $line, 2;
      $name =~ s/^\s+//; $name =~ s/\s+$//;
      if ($value) { $value =~ s/^\s+//; $value =~ s/\s+$//; }
      else { $value = ''; }

      push @ReadConfigNames, lc $name;
      push @ReadConfigValues, getInt $value;
      if ($Config{'debug'} > 7) {
         print "ReadConfigFile: Name=" . $name . ", Value=" . $value . "\n";
      }
   }
   close READCONFFILE;
}

#############################################################################

# Add / to BaseDir
unless ($BaseDir =~ m=/$=) {
   $BaseDir = $BaseDir . "/";
}

# Load main config file...
if ($Config{'debug'} > 8) {
   print "\nDefault Config:\n";
   PrintConfig();
}

CleanVars();

my $OldMailTo = $Config{'mailto'};
my $OldPrint  = $Config{'print'};

ReadConfigFile ($ConfigDir . "/logwatch.conf");
for (my $i = 0; $i <= $#ReadConfigNames; $i++) {
   if ($ReadConfigNames[$i] eq "logfile") {
      push @LogFileList, $ReadConfigValues[$i];
   } elsif ($ReadConfigNames[$i] eq "service") {
      push @ServiceList, $ReadConfigValues[$i];
   } else {
      $Config{$ReadConfigNames[$i]} = $ReadConfigValues[$i];
   }
}

CleanVars();

if ($OldMailTo ne $Config{'mailto'}) {
   $Config{'print'} = 0;
} elsif ($OldPrint ne $Config{'print'}) {
   $Config{'mailto'} = "";
}

if ($Config{'debug'} > 8) {
   print "\nConfig After Config File:\n";
   PrintConfig();
}

# Options time...

my @TempLogFileList = ();
my @TempServiceList = ();
my $Help = 0;
my $ShowVersion = 0;

$OldMailTo = $Config{'mailto'};
$OldPrint  = $Config{'print'};

GetOptions ( "d|detail=s"   => \$Config{'detail'},
             "l|logfile=s@" => \@TempLogFileList,
             "logdir=s"     => \$Config{'logdir'},
             "s|service=s@" => \@TempServiceList,
             "p|print"      => \$Config{'print'},
             "m|mailto=s"   => \$Config{'mailto'},
             "save=s"       => \$Config{'save'},
             "a|archives"   => \$Config{'archives'},
             "debug=s"      => \$Config{'debug'},
             "r|range=s"    => \$Config{'range'},
             "h|help"       => \$Help,
             "v|version"    => \$ShowVersion,
             "hostname=s"   => \$Config{'hostname'},
             "splithosts"   => \$Config{'splithosts'},
             "multiemail"   => \$Config{'multiemail'},
           ) or Usage();

$Help and Usage();

if ($ShowVersion) {
   print "Logwatch $Version (released $VDate)\n";
   exit 0;
}

CleanVars();

if ($OldMailTo ne $Config{'mailto'}) {
   $Config{'print'} = 0;
} elsif ($OldPrint ne $Config{'print'}) {
   $Config{'mailto'} = "";
}

if ($Config{'debug'} > 8) {
   print "\nCommand Line Parameters:\n   Log File List:\n";
   PrintStdArray @TempLogFileList;
   print "\n   Service List:\n";
   PrintStdArray @TempServiceList;
   print "\nConfig After Command Line Parsing:\n";
   PrintConfig();
}

if ($#TempLogFileList > -1) {
   @LogFileList = @TempLogFileList;
   for (my $i = 0; $i <= $#LogFileList; $i++) {
      $LogFileList[$i] = lc($LogFileList[$i]);
   }
   @ServiceList = ();
}

if ($#TempServiceList > -1) {
   @ServiceList = @TempServiceList;
   for (my $i = 0; $i <= $#ServiceList; $i++) {
      $ServiceList[$i] = lc($ServiceList[$i]);
   }
}

if ( ($#ServiceList == -1) and ($#LogFileList == -1) ) {
   push @ServiceList, 'all';
}

if ($Config{'debug'} > 5) {
   print "\nConfig After Everything:\n";
   PrintConfig();
}

#############################################################################

# Find out what services are defined...
my (@TempAllServices, $ThisFile, $count);

opendir(SERVICESDIR, $ConfigDir . '/services') or
   die $ConfigDir . "/services: $!";
my @services = grep !-d && /\.conf$/, readdir SERVICESDIR;
closedir SERVICESDIR;

foreach my $f (@services) {
   my $ThisService = lc $f;
   $ThisService =~ s/\.conf$//;
   push @TempAllServices, $ThisService;

   ReadConfigFile($ConfigDir . "/services/$f");

   for (my $i = 0; $i <= $#ReadConfigNames; $i++) {
      if ($ReadConfigNames[$i] eq 'logfile') {
         push @{$ServiceData{$ThisService}{'logfiles'}}, $ReadConfigValues[$i];
      } elsif ($ReadConfigNames[$i] =~ /^\*/) {
         $count++;
         $ServiceData{$ThisService}{+sprintf("%03d-%s", $count, $ReadConfigNames[$i])} = $ReadConfigValues[$i];
      } else {
         $ServiceData{$ThisService}{$ReadConfigNames[$i]} = $ReadConfigValues[$i];
      }
   }
}
my @AllServices = sort @TempAllServices;

# Find out what logfiles are defined...
opendir(LOGFILEDIR, $ConfigDir . "/logfiles") or die $ConfigDir . "/logfiles/, no such directory.\n";
while (defined($ThisFile = readdir(LOGFILEDIR))) {
   unless (-d $ConfigDir . "/logfiles/" . $ThisFile) {
      my $ThisLogFile = $ThisFile;
      if ($ThisLogFile =~ s/\.conf$//i) {
         push @AllLogFiles, $ThisLogFile;
         ReadConfigFile($ConfigDir . "/logfiles/" . $ThisFile);
         for (my $i = 0; $i <= $#ReadConfigNames; $i++) {
            if ($ReadConfigNames[$i] eq "logfile") {
               #Lets try and find the logs -mgt
               if (-e "$Config{'logdir'}/$ReadConfigValues[$i]") {
                  push @{$LogFileData{$ThisLogFile}{'logfiles'}}, $ReadConfigValues[$i];
               } elsif (-e "/var/adm/$ReadConfigValues[$i]") {
                  push @{$LogFileData{$ThisLogFile}{'logfiles'}}, "adm/$ReadConfigValues[$i]";
               } elsif (-e "/var/log/$ReadConfigValues[$i]") {
                  push @{$LogFileData{$ThisLogFile}{'logfiles'}}, "log/$ReadConfigValues[$i]";
               } else {
                  #Fallback to default even if it doesn't exist -mgt
                  push @{$LogFileData{$ThisLogFile}{'logfiles'}},
                  $ReadConfigValues[$i];
               }
            } elsif ($ReadConfigNames[$i] eq "archive") {
               push @{$LogFileData{$ThisLogFile}{'archives'}}, $ReadConfigValues[$i];
            } elsif ($ReadConfigNames[$i] =~ /^\*/) {
               $count++;
               $LogFileData{$ThisLogFile}{+sprintf("%03d-%s", $count, $ReadConfigNames[$i])} = $ReadConfigValues[$i];
            } else {
               $LogFileData{$ThisLogFile}{$ReadConfigNames[$i]} = $ReadConfigValues[$i];
            }
         }
      }
   }
}
closedir(LOGFILEDIR);

# Find out what shared functions are defined...
opendir(SHAREDDIR,$BaseDir . "scripts/shared") or die $BaseDir . "scripts/shared/, no such directory.\n";
while (defined($ThisFile = readdir(SHAREDDIR))) {
   unless (-d $BaseDir . "scripts/shared/" . $ThisFile) {
      push @AllShared, lc($ThisFile);
   }
}
closedir(SHAREDDIR);

if ($Config{'debug'} > 5) {
   print "\nAll Services:\n";
   PrintStdArray @AllServices;
   print "\nAll Log Files:\n";
   PrintStdArray @AllLogFiles;
   print "\nAll Shared:\n";
   PrintStdArray @AllShared;
}

#############################################################################

# Time to expand @ServiceList, using @LogFileList if defined...

if ((scalar @ServiceList > 1) && (grep /^all$/i, @ServiceList)) {
    # This means we are doing *all* services ... but excluding some
    my %tmphash;
    foreach my $item (@AllServices) {
      $tmphash{lc $item} = "";
    }
    foreach my $service (@ServiceList) {
      next if $service =~ /^all$/i;
      if ($service =~ /^\-(.+)$/) {
          my $offservice = $1;
          if (! exists $tmphash{lc $offservice}) {
              die "Nonexistent service to disable: $offservice\n";
          }
          delete $tmphash{lc $offservice};
      } else {
          die "Wrong configuration entry for \"Service\", if \"All\" selected, only \"-\" items are allowed\n";
      }
    }
    @ServiceList = ();
    foreach my $keys (keys %tmphash) {
      push @ServiceList, $keys;
    }
    @LogFileList = ();
} elsif ( $ServiceList[0] and ($ServiceList[0] eq 'all') and ($#ServiceList == 0) ) {
   # This means we are doing *all* services...
   @ServiceList = @AllServices;
   @LogFileList = ();
} else {
   my $ThisOne;
   while (defined($ThisOne = pop @LogFileList)) {
      unless ($LogFileData{$ThisOne}) {
         die "Logwatch is not configured to use logfile: $ThisOne\n";
      }
      foreach my $ThisService (keys %ServiceData) {
         for (my $i = 0; $i <= $#{$ServiceData{$ThisService}{'logfiles'}}; $i++) {
            if ( $ServiceData{$ThisService}{'logfiles'}[$i] eq $ThisOne ) {
               push @ServiceList,$ThisService;
            }
         }
      }
   }
   @TempServiceList = sort @ServiceList;
   @ServiceList = ();
   my $LastOne = "";
   while (defined($ThisOne = pop @TempServiceList)) {
      unless ( ($ThisOne eq $LastOne) or ($ThisOne eq 'all') or ($ThisOne =~ /^-/)) {
         unless ($ServiceData{$ThisOne}) {
            die "Logwatch does not know how to process service: $ThisOne\n";
         }
         push @ServiceList, $ThisOne;
      }
      $LastOne = $ThisOne;
   }
}

# Now lets fill up @LogFileList again...
foreach my $ServiceName (@ServiceList) {
   foreach my $LogName ( @{$ServiceData{$ServiceName}{'logfiles'} } ) {
      unless ( grep m/$LogName/, @LogFileList ) { 
         push @LogFileList, $LogName;
      }
   }
}

if ($Config{'debug'} > 7) {
   print "\n\nAll Service Data:\n";
   PrintServiceData;
   print "\nServices that will be processed:\n";
   PrintStdArray @ServiceList;
   print "\n\n";
   print "\n\nAll LogFile Data:\n";
   PrintLogFileData;
   print "\nLogFiles that will be processed:\n";
   PrintStdArray @LogFileList;
   print "\n\n";
}

#############################################################################

my $TempDir;
my $UseMkTemp = $Config{'usemktemp'};
my $MkTemp = $Config{'MkTemp'};
if ($UseMkTemp and ($MkTemp)) {
   $TempDir = `$MkTemp -d $Config{'tmpdir'}/logwatch.XXXXXXXX 2>/dev/null`;
   chomp($TempDir);
   unless (($? == 0) and $TempDir) {
      die "Failed to create $Config{'tmpdir'}/logwatch.XXXXXXXX with mktemp!!\nDoes your mktemp support the -d option??\nIf not, modify logwatch.conf accordingly.\n";
   }
   if ($Config{'debug'}>7) {
      print "\nMade Temp Dir: " . $TempDir . " with mktemp\n";
   }
} else {
   my $uid = $<;
   my $gid = (split(' ', $( ))[0];

   # Create the temporary directory...
   $TempDir = $Config{'tmpdir'} . "/logwatch." . $$;

   if ($Config{'debug'}>7) {
      print "\nMaking Temp Dir: " . $TempDir . "\n";
   }

   `rm -rf $TempDir`;
   mkdir ($TempDir,0700) or die "Failed to create TempDir: $TempDir (somebody may be attempting a root exploit!)\n";
   `chown $uid $TempDir`;
   `chgrp $gid $TempDir`;
   `chmod 0700 $TempDir`;
   unless (-d $TempDir and (not -l $TempDir)) {
      die "$TempDir not a directory (somebody is attempting a root exploit!)\n";
   }
   unless ((stat($TempDir))[4] == $uid) {
      die "$TempDir not owned by UID $uid (somebody is attempting a root exploit!)\n";
   }
   unless ((stat($TempDir))[5] == $gid) {
      die "$TempDir not owned by GID $gid (somebody is attempting a root exploit!)\n";
   }
   unless (((stat($TempDir))[2] & 07777) == 0700) {
      die "$TempDir permissions not 0700 (somebody is attempting a root exploit!)\n";
   }
   # Check to make sure nothing changed after we checked the ownership
   unless (-d $TempDir and (not -l $TempDir)) {
      die "$TempDir not a directory (somebody is attempting a root exploit!)\n";
   }
   `rm -rf $TempDir/*`;
   unless (`ls $TempDir | wc -l` == 0) {
      die "$TempDir not empty (somebody is attempting a root exploit!)\n";
   }  
}

unless ($TempDir =~ m=/$=) {
    $TempDir .= "/";
}
   
#############################################################################

# Set up the environment...

$ENV{'LOGWATCH_DATE_RANGE'} = $Config{'range'};
$ENV{'LOGWATCH_DETAIL_LEVEL'} = $Config{'detail'};
$ENV{'LOGWATCH_DEBUG'} = $Config{'debug'};
$ENV{'LOGWATCH_TEMP_DIR'} = $TempDir;
if ($Config{'hostlimit'}) {
   $ENV{'LOGWATCH_ONLY_HOSTNAME'} = $Config{'hostname'};
   $ENV{'LOGWATCH_ONLY_HOSTNAME'} =~ s/\..*//;
}
if ($Config{'debug'}>4) {
   foreach ('LOGWATCH_DATE_RANGE', 'LOGWATCH_DETAIL_LEVEL', 
            'LOGWATCH_TEMP_DIR', 'LOGWATCH_DEBUG', 'LOGWATCH_ONLY_HOSTNAME') {
      if ($ENV{$_}) {
         print "export $_='$ENV{$_}'\n";
      }
   }
}

my $LibDir = "$BaseDir/lib";
if ($ENV{PERL5LIB}) {
    # User dirs should be able to override this setting
    $ENV{PERL5LIB} = "$ENV{PERL5LIB}:$LibDir";
} else {
    $ENV{PERL5LIB} = $LibDir;
}

#############################################################################

unless ($Config{'logdir'} =~ m=/$=) {
   $Config{'logdir'} .= "/";
}

# Okay, now it is time to do pre-processing on all the logfiles...

my $LogFile;
foreach $LogFile (@LogFileList) {
   next if ($LogFile eq 'none');
	if (!defined($LogFileData{$LogFile}{'logfiles'})) {
		print "*** Error: There is no logfile defined. Do you have a " . $BaseDir . "conf/logfiles/" . $LogFile . ".conf file ?\n";
		next;
	}
   @FileList = @{$LogFileData{$LogFile}{'logfiles'}};
   if ($Config{'archives'} == 1) {
      push @FileList, $TempDir . $LogFile . "-archive";
      my $Archive;
      foreach $Archive (@{$LogFileData{$LogFile}{'archives'}}) {
         my $DestFile =  $TempDir . $LogFile . "-archive";
         unless ($Archive =~ m=^/=) {
            $Archive = ($Config{'logdir'} . $Archive);
         }
         if ($Archive =~ m/gz$/) {
            `/bin/zcat $Archive 2>/dev/null >> $DestFile`;
         } else {
            `/bin/cat $Archive 2>/dev/null >> $DestFile`;
         }
      }
   }
   my $FileText = "";
   foreach $ThisFile (@FileList) {
      if ($ThisFile =~ m=^/=) {
         $FileText .= ($ThisFile . " ");
      } else {
         $FileText .= ( $Config{'logdir'} . $ThisFile . " ");
      }
   }
   my $FilterText = " 2>/dev/null ";
   foreach (sort keys %{$LogFileData{$LogFile}}) {
      my $cmd = $_;
      if ($cmd =~ s/^\d+-\*//) {
         $FilterText .= ("| $BaseDir" . "scripts/shared/$cmd '$LogFileData{$LogFile}{$_}'" );
      } elsif ($cmd =~ s/^\$//) {
         $ENV{$cmd} = $LogFileData{$LogFile}{$_};
         if ($Config{'debug'}>4) {
            print "export $cmd='$LogFileData{$LogFile}{$_}'\n";
         }
      }
   }
   if (opendir (LOGDIR,$BaseDir . "scripts/logfiles/" . $LogFile)) {
      foreach (sort readdir(LOGDIR)) {
         unless ( -d $BaseDir . "scripts/logfiles/$LogFile/$_") {
            $FilterText .= ("| $BaseDir" . "scripts/logfiles/$LogFile/$_");
         }
      }
      closedir (LOGDIR);
   }
   if ($FileText) {
      my $Command = $FileText . $FilterText . ">" . $TempDir . $LogFile;
      if ($Config{'debug'}>4) {
         print "\nPreprocessing LogFile: " . $LogFile . "\n" . $Command . "\n";
      }
      if ($LogFile !~ /^[-_\w\d]+$/) {
         print STDERR "Unexpected filename: [[$LogFile]]. Not used\n"
      } else {
         `/bin/cat $Command`;
      }
   }
}

#populate the host lists if we're splitting hosts
my @hosts;
if ($Config{'splithosts'} eq 1) {
   my $newlogfile;
   my @logarray;
   opendir (LOGDIR,$TempDir) || die "Cannot open dir";
   @logarray = readdir(LOGDIR);
   closedir (LOGDIR);
   my $ecpcmd = ("| $BaseDir" . "scripts/shared/hostlist");
   foreach $newlogfile (@logarray) {
     my $eeefile = ("$TempDir" . "$newlogfile");
     if ((!(-d $eeefile)) && (!($eeefile =~ m/-archive/))) {
        `/bin/cat $eeefile $ecpcmd`;
     }
   }
   #read in the final host list
   open (HOSTFILE,"$TempDir/hostfile") || die $!;
   @hosts = <HOSTFILE>;
   close (HOSTFILE);
   chomp @hosts;
   @hosts = sort(@hosts);
}

#############################################################################

my $report_finish = "\n ###################### LogWatch End ######################### \n\n";
my $printing = '';
my $emailopen = '';

sub initprint {
   return if $printing;
   if ($Config{'print'} eq 1) {
      *OUTFILE = *STDOUT;
   } elsif ($Config{'save'} ne "") {
      open(OUTFILE,">" . $Config{'save'}) or die "Can't open output file: $Config{'save'}\n";
   } elsif ($OSname eq "SunOS") {
      #Solaris mail doesn't know -s -mgt
      if (($Config{'multiemail'} eq 1) || ($emailopen eq "")) {
         open(OUTFILE,"|$Config{'mailer'} $Config{'mailto'}") or die "Can't execute /bin/mail\n";
         print OUTFILE "From: LogWatcher\n";
         print OUTFILE "To: $Config{'mailto'}\n";
         print OUTFILE "Subject: LogWatch for $Config{'hostname'}\n\n";
         if (($Config{'splithosts'} eq 1) && ($Config{'multiemail'} eq 0)) {
            print OUTFILE "Reporting on hosts: @hosts\n";
         }
         $emailopen = 'y';
      }
   } else {
      if (($Config{'multiemail'} eq 1) || ($emailopen eq "")) {
         open(OUTFILE,"|$Config{'mailer'} -s \"LogWatch for $Config{'hostname'}\" " . $Config{'mailto'}) or die "Can't execute /bin/mail\n";
         if (($Config{'splithosts'} eq 1) && ($Config{'multiemail'} eq 0)) {
            print OUTFILE "Reporting on hosts: @hosts\n";
         }
         $emailopen = 'y';
      }
   }
   $printing = 'y';
   print OUTFILE "\n ################### LogWatch $Version ($VDate) #################### \n";
   print OUTFILE "       Processing Initiated: " . localtime(time) . "\n";
   print OUTFILE "       Date Range Processed: $Config{'range'}\n";
   print OUTFILE "     Detail Level of Output: $Config{'detail'}\n";
   print OUTFILE "          Logfiles for Host: $Config{'hostname'}\n";
   print OUTFILE " ################################################################ \n";
}

sub parselogs {
   my $Service;
   foreach $Service (sort @ServiceList) {
      $ENV{'PRINTING'} = $printing;
      @FileList = @{$ServiceData{$Service}{'logfiles'}};
      my $FileText = "";
      foreach $ThisFile (@FileList) {
         if (-s $TempDir . $ThisFile) {
            $FileText .= ( $TempDir . $ThisFile . " ");
         }
      }
      my $FilterText = " ";
      foreach (sort keys %{$ServiceData{$Service}}) {
         my $cmd = $_;
         if ($cmd =~ s/^\d+-\*//) {
            $FilterText .= ("$BaseDir" . "scripts/shared/$cmd '$ServiceData{$Service}{$_}' |" );
         } elsif ($cmd =~ s/^\$//) {
            $ENV{$cmd} = $ServiceData{$Service}{$_};
            if ($Config{'debug'}>4) {
               print "export $cmd='$ServiceData{$Service}{$_}'\n";
            }
         }
      }
# ECP - insert the host stripping now
      my $HostStrip = " ";
      if ($Config{'splithosts'} eq 1) {
         $HostStrip .= ("$BaseDir" . "scripts/shared/onlyhost");
      }
      if ( -f $BaseDir . "scripts/services/" . $Service ) {
         $FilterText .= ("" . $BaseDir . "scripts/services/" . $Service );
      }
      else {
         die "Can't open: " . $BaseDir . "scripts/services/" . $Service;
      }

      my $Command = '';
      if ($FileList[0] eq 'none') {
         $Command = " $FilterText 2>&1 "; 
      } elsif ($FileText) {
         if ($HostStrip ne " ") {
            $Command = " ( /bin/cat $FileText | $HostStrip | $FilterText) 2>&1 "; 
         } else {
            $Command = " ( /bin/cat $FileText | $FilterText) 2>&1 "; 
         }
      }
   
      if ($Command) {
         if ($Config{'debug'}>4) {
            print "\nProcessing Service: " . $Service . "\n" . $Command . "\n";
         }
         open (TESTFILE,$Command . " |");
         my $ThisLine;
         my $has_output = 0;
         while (defined ($ThisLine = <TESTFILE>)) {
            next if ((not $printing) and $ThisLine =~ /^\s*$/);
            initprint();
            if (($has_output == 0) and ($ServiceData{$Service}{'title'})) {
               print OUTFILE "\n --------------------- $ServiceData{$Service}{'title'} Begin ------------------------ \n\n";
               $has_output = 1;
            }
            print OUTFILE $ThisLine;
         }
         close (TESTFILE);
         if ($has_output and $ServiceData{$Service}{'title'}) {
            print OUTFILE "\n ---------------------- $ServiceData{$Service}{'title'} End ------------------------- \n\n";
         }
      }
   }

   print OUTFILE $report_finish if ($printing);
   if ($Config{'multiemail'} eq 1) {
      close(OUTFILE) unless ($Config{'print'} eq 1);
   }
}

if ($Config{'splithosts'} eq 1) {
   my $Host;
   foreach $Host (@hosts) {
      $printing = '';
      $ENV{'LOGWATCH_ONLY_HOSTNAME'} = $Host;
      $ENV{'LOGWATCH_ONLY_HOSTNAME'} =~ s/\..*//;
      $Config{'hostname'} = $Host;
      parselogs();
   } # ECP
} else {
   parselogs();
}
close(OUTFILE) unless ($Config{'print'} eq 1);
#############################################################################

# Get rid of temp directory...
if ($Config{'debug'}<100) {
   `rm -rf $TempDir`;
}

exit(0);

# vi: shiftwidth=3 tabstop=3 et

