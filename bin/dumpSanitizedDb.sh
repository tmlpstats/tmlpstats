#!/usr/bin/env bash

##
# Create a sanitized dump of the database
#

die() {
    echo $1
    exit 1
}

DATE=`date +%Y-%m-%d`

SOURCE="$HOME/tmlpstats.git/src"
STAGE="/var/www/stage.tmlpstats.com"
PROD="/var/www/tmlpstats.com"

$SOURCE/../bin/stage-lock.sh || exit 1

echo ""
echo "Snapping the database"
# Setup up temporary .my.cnf file
echo "[mysqldump]" > $HOME/.my.cnf
grep 'DB_USERNAME=' $PROD/.env | awk -F= '{print "user="$2}' >> $HOME/.my.cnf
grep 'DB_PASSWORD=' $PROD/.env | awk -F= '{print "password="$2}' >> $HOME/.my.cnf
echo "[mysql]" >> $HOME/.my.cnf
grep 'DB_USERNAME=' $STAGE/.env | awk -F= '{print "user="$2}' >> $HOME/.my.cnf
grep 'DB_PASSWORD=' $STAGE/.env | awk -F= '{print "password="$2}' >> $HOME/.my.cnf

$SOURCE/../bin/snap.sh

echo ""
echo "Running sanitization sctip"
cd $STAGE/
php artisan db:sanitize

mysqldump tmlpstats_stage > "$HOME/tmlpstats_sanitized_$DATE.sql"

rm -f $HOME/.my.cnf

$SOURCE/../bin/stage-lock.sh release
