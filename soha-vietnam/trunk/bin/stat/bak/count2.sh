#!/bin/sh

date0=`date  +%Y%m%d`
date1=`date -d "1 days ago" +%Y%m%d`
tempdir='/usr/local/happyfish/stat/renren'
statdb='renren_islandv2_log_stat'


/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58 ${statdb} -e "delete from day_user_retention where log_time=${date1}"

/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58 ${statdb} -e "insert into  day_user_retention(log_time)  values (${date1})"

s=0
j=0
k=0
n=0

for (( i=1;  i<=30;  i=i+1 ))
do
    s=$(date -d "$i day ago 00:00:00" +%s)
    #s=`expr $s - 172800`
    j=`expr $s - 86400`
    n=`expr $n + 1`
    k=`cat ${tempdir}/${date1}/all-101-${date1}.log | awk '$4>"'$j'" && $4 < "'$s'" {print $1}'     | wc -l `
/usr/local/mysql/bin/mysql -uworker -p'r$6i7kP#xp' -h 192.168.0.58 ${statdb} -e "update day_user_retention set day_${n}=${k} where log_time=${date1}"
done
