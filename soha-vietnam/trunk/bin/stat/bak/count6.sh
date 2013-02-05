#!/bin/sh

date1=`date -d "1 days ago" +%Y%m`
tempdir='/usr/local/happyfish/stat/renren'
statdb='renren_islandv2_log_stat'

abc=`cat ${tempdir}/${date1}*/all-101*  |  awk '{print $3}' | sort | uniq -c |   sort -n  -k 1 |  wc -l`

/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58 ${statdb} -e "insert into month_main  values ($date1,$abc)"
