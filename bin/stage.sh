#!/usr/bin/env bash

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

SOURCE="$HOME/tmlpstats.git"
DEST="/var/www/stage.tmlpstats.com"
PROD="/var/www/tmlpstats.com"
ROLLBACK="$HOME/tmlpstats.rollback"

if [ "$1" == "rollback" ]; then
    rsync -av --delete --filter='protect storage/framework/down' $ROLLBACK/ $DEST
    exit 0;
fi

# Leave rollback as is. It should contain whatever was last deployed to production

cd $SOURCE/
echo "Pulling latest sources"
git pull --rebase

echo ""
echo "Running composer"
# Do actual deploy
composer install --no-dev --optimize-autoloader

echo ""
echo "Snapping the database"
$SOURCE/bin/snap.sh

echo ""
echo "Copying file archive"
rsync -av --delete $PROD/storage/app/* $DEST/storage/app/

echo ""
echo "Running migrations"
cd $DEST/
php artisan migrate

echo ""
echo "Syncing files"
rsync -av --delete --filter='protect .env' \
                   --filter='protect storage/framework/sessions/*' \
                   --filter='storage/framework/down' \
                   --filter='protect storage/logs/*' \
                   --filter='protect storage/app/*' \
                   --filter='protect public/error_log' \
                   --exclude='.git*' \
                   --exclude='composer.*' \
                   --exclude='readme.md' \
                   --exclude='.env.example' \
                   --exclude='.editorconfig' \
                   --exclude='bin' \
                   --exclude='tests' \
                   --exclude='Vagrantfile' \
                   --exclude='gulpfile.js' \
                   --exclude='package.json' \
                   --exclude='phpspec.yml' \
                   --exclude='phpunit.xml' \
                   $SOURCE/ $DEST

echo ""
echo "Fixing file permisssions"
sudo chmod -R o+w $DEST/storage
