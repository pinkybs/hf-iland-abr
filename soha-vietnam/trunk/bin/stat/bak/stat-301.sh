#!/bin/sh

date1=`date -d "1 days ago" +%Y%m%d`
#date1='20110517'
prefix='301'
tempdir='/usr/local/services/tools/stat-data'
rm -rf  ${tempdir}/${prefix}/${date1}
mkdir -p -m 777  ${tempdir}/${prefix}/${date1}
cd ${tempdir}/${prefix}/${date1}

/usr/bin/wget -q -O  ${prefix}-${date1}.log.02   http://10.135.129.208/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.03   http://10.135.129.148/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.04   http://10.135.129.149/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.05   http://10.135.130.86/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.06   http://10.135.130.87/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.07   http://10.135.130.88/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.08   http://10.135.130.89/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.09   http://10.135.136.139/debug/${prefix}-${date1}.log


/usr/bin/sort -m -t " " -k 1 -o all-${prefix}-.log   ${prefix}-${date1}.log.02  ${prefix}-${date1}.log.03  ${prefix}-${date1}.log.04  ${prefix}-${date1}.log.05  ${prefix}-${date1}.log.06  ${prefix}-${date1}.log.07   ${prefix}-${date1}.log.08  ${prefix}-${date1}.log.09

num2=`cat  all-${prefix}-.log  | awk '{print $4}'    |  grep 2 |  wc -l`
num21=`cat  all-${prefix}-.log  | awk '{print $4,$5}' |  grep '2 1' | wc -l`
num22=`cat  all-${prefix}-.log  | awk '{print $4,$5}' |  grep '2 2' | wc -l`

num3=`cat  all-${prefix}-.log  | awk '{print $4}'    |  grep 3 |  wc -l`
num31=`cat  all-${prefix}-.log  | awk '{print $4,$5}' |  grep '3 1' | wc -l`
num32=`cat  all-${prefix}-.log  | awk '{print $4,$5}' |  grep '3 2' | wc -l`

num4=`cat  all-${prefix}-.log  | awk '{print $4}'    |  grep 4 |  wc -l`
num41=`cat  all-${prefix}-.log  | awk '{print $4,$5}' |  grep '4 1' | wc -l`
num42=`cat  all-${prefix}-.log  | awk '{print $4,$5}' |  grep '4 2' | wc -l`

printf "2\t${num2}\t${num21}\t${num22}\n" >> stat-${prefix}-result
printf "3\t${num3}\t${num31}\t${num32}\n" >> stat-${prefix}-result
printf "4\t${num4}\t${num41}\t${num42}\n" >> stat-${prefix}-result

