#!/bin/bash

# Setup:
#   1) Install composer
#       $ mkdir ~/common
#       $ cd ~/common
#       $ curl -sS https://getcomposer.org/installer | /ramdisk/php/54/bin/php54-cli
#
# Deploy Process:
#    1) Pull latest changes from github
#       $ cd tmlpstats
#       $ git pull
#    2) Run deploy script
#       $ cd ..
#       $ ./deploy.sh
#
# Rollback Process:
#    1) Run deploy script with rollback option
#       $ ./deploy.sh rollback
#

SOURCE="$HOME/tmlpstats.git/tmlpstats"
DEST="$HOME/public_html/stage"
PROD="$HOME/public_html/tmlpstats"
ROLLBACK="$HOME/tmlpstats.git/rollback"

if [ "$1" == "rollback" ]; then

    rm -rf $DEST/*
    cp -a $ROLLBACK/* $DEST/
    exit 0;
fi

# Leave rollback as is. It should contain whatever was last deployed to production

cd $SOURCE/
echo "Pulling latest sources"
git pull --rebase

echo ""
echo "Running composer"
# Do actual deploy
sed -i.bak 's/php artisan/php-cli artisan/g' composer.json # workaround issue with artisan an bluehost
php-cli ~/common/composer.phar install --no-dev --optimize-autoloader
mv composer.json.bak composer.json # clean up

echo ""
echo "Snapping the database"
cp $HOME/tmlpstats.git/.my.cnf $HOME/.my.cnf
$SOURCE/bin/snap.sh
rm $HOME/.my.cnf

echo ""
echo "Copying file archive"
rsync -av --delete $PROD/storage/app/* $DEST/storage/app/

echo ""
echo "Running migrations"
cd $DEST/
php-cli artisan migrate


echo ""
echo "Syncing files"
rsync -av --delete --filter='protect .env' \
                   --filter='protect storage/framework/sessions/*' \
                   --filter='protect storage/logs/*' \
                   --filter='protect storage/app/*' \
                   --filter='protect public/error_log' \
                   --exclude='.git*' \
                   --exclude='composer.*' \
                   --exclude='readme.md' \
                   --exclude='.env.example' \
                   --exclude='.editorconfig' \
                   --exclude='bin' \
                   $SOURCE/ $DEST
