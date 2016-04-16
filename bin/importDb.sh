#!/usr/bin/env bash

if [ "$#" -ne 1 ]; then
    echo "You must pass the path to sql dump file"
    exit
fi

if [ "$HOSTNAME" != "vagrant.tmlpstats.com" ]; then
    echo "This script can only be run on vagrant instances"
    exit
fi

WWW=/var/www/tmlpstats.com
export=/vagrant/export/$1

db_user=`grep 'DB_USERNAME=' $WWW/.env | awk -F= '{print "user="$2}'`
db_pass=`grep 'DB_PASSWORD=' $WWW/.env | awk -F= '{print "password="$2}'`
db_name=`grep 'DB_DATABASE=' $WWW/.env | awk -F= '{print $2}'`

# Setup up temporary .my.cnf file
echo "[mysql]" > $HOME/.my.cnf
echo $db_user >> $HOME/.my.cnf
echo $db_pass >> $HOME/.my.cnf

mysql $db_name < $export
