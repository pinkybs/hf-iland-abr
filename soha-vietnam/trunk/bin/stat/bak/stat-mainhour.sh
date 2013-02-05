#!/bin/sh

date1=`date -d "1 days ago" +%Y%m%d`
#date1='20110518'

time1=`date -d "1 day ago 00:00:00" +%s`

statdb='taobao_island_log_stat'
tempdir='/home/admin/data/stat-data'

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

for score in `cat ${tempdir}/100/${date1}/all-100-${date1}.log | awk '{print $1}'`
    do
        k1=`expr ${score} - ${time1}`
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

t0=0
t1=0
t2=0
t3=0
t4=0
t5=0
t6=0
t7=0
t8=0
t9=0
t10=0
t11=0
t12=0
t13=0
t14=0
t15=0
t16=0
t17=0
t18=0
t19=0
t20=0
t21=0
t22=0
t23=0

r1=0
r2=0

for score2 in `cat ${tempdir}/101/${date1}/all-101-${date1}.log  | awk '{print $1}'`
    do
        r1=`expr ${score2} - ${time1}`
        r2=`expr $r1 / 3600`

case "$r2" in
0) t0=`expr $t0 + 1`     ;;
1) t1=`expr $t1 + 1`     ;;
2) t2=`expr $t2 + 1`     ;;
3) t3=`expr $t3 + 1`     ;;
4) t4=`expr $t4 + 1`     ;;
5) t5=`expr $t5 + 1`     ;;
6) t6=`expr $t6 + 1`     ;;
7) t7=`expr $t7 + 1`     ;;
8) t8=`expr $t8 + 1`     ;;
9) t9=`expr $t9 + 1`     ;;
10) t10=`expr $t10 + 1`  ;;
11) t11=`expr $t11 + 1`  ;;
12) t12=`expr $t12 + 1`  ;;
13) t13=`expr $t13 + 1`  ;;
14) t14=`expr $t14 + 1`  ;;
15) t15=`expr $t15 + 1`  ;;
16) t16=`expr $t16 + 1`  ;;
17) t17=`expr $t17 + 1`  ;;
18) t18=`expr $t18 + 1`  ;;
19) t19=`expr $t19 + 1`  ;;
20) t20=`expr $t20 + 1`  ;;
21) t21=`expr $t21 + 1`  ;;
22) t22=`expr $t22 + 1`  ;;
*)  t23=`expr $t23 + 1`  ;;  
esac

done

/home/mysql/bin/mysql -u worker -p'r$6i7kP#xp' -h 192.168.15.9  ${statdb}  -s  -e "insert  into day_main_hour values (${date1}00,${s0},${t0}),(${date1}01,${s1},${t1}),(${date1}02,${s2},${t2}),(${date1}03,${s3},${t3}),(${date1}04,${s4},${t4}),(${date1}05,${s5},${t5}),(${date1}06,${s6},${t6}),(${date1}07,${s7},${t7}),(${date1}08,${s8},${t8}),(${date1}09,${s9},${t9}),(${date1}10,${s10},${t10}),(${date1}11,${s11},${t11}),(${date1}12,${s12},${t12}),(${date1}13,${s13},${t13}),(${date1}14,${s14},${t14}),(${date1}15,${s15},${t15}),(${date1}16,${s16},${t16}),(${date1}17,${s17},${t17}),(${date1}18,${s18},${t18}),(${date1}19,${s19},${t19}),(${date1}20,${s20},${t20}),(${date1}21,${s21},${t21}),(${date1}22,${s22},${t22}),(${date1}23,${s23},${t23})"

