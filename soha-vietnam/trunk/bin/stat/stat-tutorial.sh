#!/bin/sh

#date1=`date -d "1 days ago" +%Y%m%d`
date1='20110715'

prefix='tutorial'
tempdir='/data/stat/stat-data'
rm -rf  ${tempdir}/${prefix}/${date1}
mkdir -p -m 777  ${tempdir}/${prefix}/${date1}
cd ${tempdir}/${prefix}/${date1}

/usr/bin/wget -q -O  ${prefix}-${date1}.log.01   http://happyislandvcc.snsplus.com/debug/${prefix}-${date1}.log

/bin/sort -m -t " " -k 1 -o all-${prefix}-${date1}.log   ${prefix}-${date1}.log.01  

cd /data/website/island/vietnam/bin
/usr/local/php-cgi/bin/php /data/website/island/vietnam/bin/stat-tutorial.php
