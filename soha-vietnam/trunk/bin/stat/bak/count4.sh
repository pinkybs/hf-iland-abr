#!/bin/sh

date1=`date -d "1 days ago" +%Y%m%d`
date2=`date -d "1 day ago 00:00:00" +%s`
tempdir='/usr/local/happyfish/stat/renren'
statdb='renren_islandv2_log_stat'

s0=0
s1=0
s2=0
s3=0
s4=0
s5=0
s6=0
s7=0
s8=0
s9=0
s10=0
s11=0
s12=0
s13=0
s14=0
s15=0
s16=0
s17=0
s18=0
s19=0
s20=0
s21=0
s22=0
s23=0

k1=0
k2=0

for score in `cat ${tempdir}/${date1}/all-100-${date1}.log  | awk '{print $1}'`
    do
        k1=`expr $score - $date2`
        k2=`expr $k1 / 3600`

case "$k2" in
0) s0=`expr $s0 + 1`     ;;
1) s1=`expr $s1 + 1`     ;;
2) s2=`expr $s2 + 1`     ;;
3) s3=`expr $s3 + 1`     ;;
4) s4=`expr $s4 + 1`     ;;
5) s5=`expr $s5 + 1`     ;;
6) s6=`expr $s6 + 1`     ;;
7) s7=`expr $s7 + 1`     ;;
8) s8=`expr $s8 + 1`     ;;
9) s9=`expr $s9 + 1`     ;;
10) s10=`expr $s10 + 1`  ;;
11) s11=`expr $s11 + 1`  ;;
12) s12=`expr $s12 + 1`  ;;
13) s13=`expr $s13 + 1`  ;;
14) s14=`expr $s14 + 1`  ;;
15) s15=`expr $s15 + 1`  ;;
16) s16=`expr $s16 + 1`  ;;
17) s17=`expr $s17 + 1`  ;;
18) s18=`expr $s18 + 1`  ;;
19) s19=`expr $s19 + 1`  ;;
20) s20=`expr $s20 + 1`  ;;
21) s21=`expr $s21 + 1`  ;;
22) s22=`expr $s22 + 1`  ;;
*)  s23=`expr $s23 + 1`  ;;  
esac

done


/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}00,${s0},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}01,${s1},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}02,${s2},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}03,${s3},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}04,${s4},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}05,${s5},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}06,${s6},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}07,${s7},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}08,${s8},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}09,${s9},0)"

/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}10,${s10},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}11,${s11},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}12,${s12},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}13,${s13},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}14,${s14},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}15,${s15},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}16,${s16},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}17,${s17},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}18,${s18},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}19,${s19},0)"

/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}20,${s20},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}21,${s21},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}22,${s22},0)"
/usr/local/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.0.58  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}23,${s23},0)"
