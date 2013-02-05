#!/bin/sh

date0=`date  +%Y%m%d`
date1=`date -d "1 days ago" +%Y%m%d`
statdb='renren_islandv2_log_stat'

/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58 ${statdb} -e "delete from  day_user_retention_rate where log_time=${date1}"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58 ${statdb} -e "insert into  day_user_retention_rate(log_time)  values (${date1})"

s=0
x=0
y=2
z=0
ave=0

for (( i=1;  i<=30;  i=i+1 ))
do
    s=$(date -d "$i days ago" +%Y%m%d)
    x=`/usr/local/mysql/bin/mysql -uworker -p'r$6i7kP#xp' -h 192.168.0.58 -s ${statdb} -s -e "select day_${i} from day_user_retention where log_time = ${date1}"`
    y=`expr $i + 1`
    k=`/usr/local/mysql/bin/mysql -uworker -p'r$6i7kP#xp' -h 192.168.0.58 -s ${statdb} -s -e "select add_user from day_main where log_time = $(date -d "$y days ago" +%Y%m%d)"`
   
    ave=`echo "scale=2;$x/$k"|bc`
    z=`echo $ave*100|bc`
 
    /usr/local/mysql/bin/mysql -uworker -p'r$6i7kP#xp' -h 192.168.0.58 ${statdb} -e "update day_user_retention_rate set day_${i}=${z} where log_time=${date1}"

done
