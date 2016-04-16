#!/usr/bin/env bash

##
# Create a dump of the database
#

die() {
    echo $1
    exit 1
}

DATE=`date +%Y-%m-%d_%H-%M-%S`

SOURCE="$HOME/tmlpstats.git/src"
PROD="/var/www/tmlpstats.com"

export="$HOME/tmlpstats_dump_$DATE.sql"

$SOURCE/../bin/stage-lock.sh || exit 1

echo ""
echo "Dumping the database to $export"

db_user=`grep 'DB_USERNAME=' $PROD/.env | awk -F= '{print "user="$2}'`
db_pass=`grep 'DB_PASSWORD=' $PROD/.env | awk -F= '{print "password="$2}'`
db_name=`grep 'DB_DATABASE=' $PROD/.env | awk -F= '{print $2}'`

# Setup up temporary .my.cnf file
echo "[mysqldump]" > $HOME/.my.cnf
echo $db_user >> $HOME/.my.cnf
echo $db_pass >> $HOME/.my.cnf

mysqldump $db_name > $export

rm -f $HOME/.my.cnf

$SOURCE/../bin/stage-lock.sh release
