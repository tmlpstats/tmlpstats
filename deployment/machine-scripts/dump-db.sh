#!/bin/bash

docker exec -ti `docker ps | grep website_mysql | awk '{print $1}'` bash -c ' 
conf=/tmp/my.cnf
echo "[mysqldump]" > $conf
echo "user=root" >> $conf
echo "password=$MYSQL_ROOT_PASSWORD" >> $conf
mysqldump --defaults-extra-file=$conf -u root tmlpstats_main'
