#!/bin/bash

# Backup the site into a single tar file that will be placed in this directory
database=ffc1
user=ffcuser
password='3:&fTH34'

theme_dir=`pwd -P`
wordpress_root=`(cd $theme_dir/../../..; pwd)`
wpcontent=${wordpress_root}/wp-content

pushd $wordpress_root
mysqldump -u${user} -p${password} ${database} > ${wpcontent}/${database}_dump.sql
cp wp-config.php ${wpcontent}
tfile=wp-content.${database}.tar.gz
tar -czf $tfile wp-content
mv $tfile $theme_dir
popd
