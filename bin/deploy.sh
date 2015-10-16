#!/usr/bin/env bash

##
# Deploy website
#

SOURCE="$HOME/tmlpstats.git/src"
DEST="/var/www/tmlpstats.com"
ROLLBACK="$HOME/tmlpstats.rollback"

cd $DEST/
php artisan down

if [ "$1" == "rollback" ]; then
    rsync -av --delete --filter='protect storage/framework/down' $ROLLBACK/ $DEST
    php artisan up
    exit 0;
fi

# Setup rollback copy
echo ""
echo "Backing up for rollback"
rm -rf $ROLLBACK
rsync -aq --exclude='storage/framework/down' $DEST/ $ROLLBACK

# Do actual deploy
echo ""
echo "Running composer"
cd $SOURCE/
composer install --no-dev --optimize-autoloader

echo ""
echo "Running migrations"
cd $DEST/
php artisan migrate

echo ""
echo "Syncing files"
rsync -av --delete --filter='protect .env' \
                   --filter='protect storage/framework/sessions/*' \
                   --filter='protect storage/framework/down' \
                   --filter='protect storage/logs/*' \
                   --filter='protect storage/app/*' \
                   --filter='protect public/error_log' \
                   --exclude='bin' \
                   --exclude='tests' \
                   --exclude='.editorconfig' \
                   --exclude='.env.example' \
                   --exclude='.git*' \
                   --exclude='composer.*' \
                   --exclude='gulpfile.js' \
                   --exclude='package.json' \
                   --exclude='phpspec.yml' \
                   --exclude='phpunit.xml' \
                   --exclude='readme.md' \
                   --exclude='Vagrantfile' \
                   $SOURCE/ $DEST

echo ""
echo "Fixing file permisssions"
sudo chmod -R o+w $DEST/storage

php artisan up
