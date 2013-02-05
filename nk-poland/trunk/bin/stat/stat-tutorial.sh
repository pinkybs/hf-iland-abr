#!/bin/sh

date1=`date -d "1 days ago" +%Y%m%d`
#date1='20110715'

prefix='tutorial'
tempdir='/home/admin/stat/stat-data'
rm -rf  ${tempdir}/${prefix}/${date1}
mkdir -p -m 777  ${tempdir}/${prefix}/${date1}
cd ${tempdir}/${prefix}/${date1}

/usr/bin/wget -q -O  ${prefix}-${date1}.log.01   http://79.125.84.45/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.02   http://46.137.132.13/debug/${prefix}-${date1}.log
/usr/bin/wget -q -O  ${prefix}-${date1}.log.03   http://46.137.47.44/debug/${prefix}-${date1}.log

/bin/sort -m -t " " -k 1 -o all-${prefix}-${date1}.log   ${prefix}-${date1}.log.01   ${prefix}-${date1}.log.02   ${prefix}-${date1}.log.03

cd /home/admin/website/island/poland/bin
/home/admin/apps/php-cgi/bin/php /home/admin/website/island/poland/bin/stat-tutorial.php
