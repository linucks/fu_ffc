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


# To restore from the backup, use the ffc_dump.sql file in the wp-content directory and the following commands
#mysql -u root -p
#DROP database ffc1;
#CREATE DATABASE ffc1;
#USE ffc1;
#GRANT ALL PRIVILEGES ON ffc1.* TO "ffcuser"@"localhost" IDENTIFIED BY "3:&fTH34";
#FLUSH PRIVILEGES;
#SOURCE ./ffc1_dump.sql;
