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

SOURCE='tmlpstats'
DEST='../public_html/stage'
ROLLBACK='rollback'

if [ "$1" == "rollback" ]; then

    rm -rf $DEST/*
    cp -a $ROLLBACK/* $DEST/
    exit 0;
fi

# Leave rollback as is. It should contain whatever was last deployed to production

# Do actual deploy
cd $SOURCE/
sed -i.bak 's/php artisan/php-cli artisan/g' composer.json # workaround issue with artisan an bluehost
php-cli ~/common/composer.phar install --no-dev --optimize-autoloader
mv composer.json.bak composer.json # clean up

./snap.sh

php artisan migrate
cd ../

rsync -av --delete --filter='protect .env' --filter='protect storage/framework/sessions/*' --filter='protect storage/logs/*' --filter='protect storage/app/*' --filter='protect public/error_log' --exclude='.git*' --exclude='composer.*' --exclude='readme.md' --exclude='.env.example' --exclude='bin' $SOURCE/ $DEST
