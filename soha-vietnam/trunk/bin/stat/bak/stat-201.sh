#!/bin/sh

date1=`date -d "1 days ago" +%Y%m%d`
#date1='20110517'
prefix='201'
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

cd /data/website/island_normal/bin/
/usr/local/services/php/bin/php -f /data/website/island_normal/bin/shoplog.php ${tempdir}/${prefix}/${date1}/all-${prefix}-.log 1

