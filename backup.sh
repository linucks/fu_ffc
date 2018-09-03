#!/bin/bash

# Backup the site into a single tar file
database=ffc1
user=ffcuser
password='3:&fTH34'

wordpress_root=`(cd ../../..; pwd)`
wpcontent=${wordpress_root}/wp-content

theme_dir=`pwd`
pushd $wordpress_root
mysqldump -u${user} -p${password} ${database} > ${wpcontent}/${database}_dump.sql
cp wp-config.php ${wpcontent}
tfile=wp-content.${database}.tar.gz
tar -czf $tfile wp-content
mv $tfile $theme_dir
popd
