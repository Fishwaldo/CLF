#!/bin/sh

if [ $# -lt 2 ]; then
	echo "Usage parselog.sh hostname datespec"
	exit 1
fi

temp_log_dir="/var/tmp/var/log"
hostspec=$1
datespec=$2

configdir=`/etc/log.d/bin/getconfig $hostspec`

if [ -d $temp_log_dir ]; then
	rm -rf $temp_log_dir
fi

if [ "$configdir" == "Error: no such system" ]; then
	echo "Error: no such system: $hostspec"
	exit 1
fi

mkdir -p $temp_log_dir;

/etc/log.d/bin/dumplog.pl $hostspec $datespec

rm -f /etc/log.d/conf
rm -f /etc/log.d/scripts
ln -s /etc/log.d/configs/$configdir/conf /etc/log.d/conf
ln -s /etc/log.d/configs/$configdir/scripts /etc/log.d/scripts

/etc/log.d/bin/logwatch.pl --print | /etc/log.d/bin/storelog.pl $hostspec - $datespec

rm -rf $temp_log_dir