#!/bin/bash

# Backup the site into a single tar file
database=ffc1
user=ffcuser
password='3:&fTH34'

wordpress_root=`(cd ../../..; pwd)`
wpcontent=${wordpress_root}/wp-content

pushd $wordpress_root
mysqldump -u${user} -p${password} ${database} > ${wpcontent}/${database}_dump.sql
cp wp-config.php ${wpcontent}
tar -czf wp-content.${database}.tar.gz wp-content
popd
