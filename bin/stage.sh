#!/usr/bin/env bash

##
# Stage website
#

die() {
    echo $1
    exit 1
}

SOURCE="$HOME/tmlpstats.git/src"
DEST="/var/www/stage.tmlpstats.com"
PROD="/var/www/tmlpstats.com"
ROLLBACK="$HOME/tmlpstats.rollback"

$SOURCE/../bin/stage-lock.sh || exit 1

if [ "$1" == "rollback" ]; then
    rsync -av --delete --filter='protect storage/framework/down' $ROLLBACK/ $DEST
    $SOURCE/../bin/stage-lock.sh release
    exit 0;
fi

# Leave rollback as is. It should contain whatever was last deployed to production

cd $SOURCE/
echo "Pulling latest sources"
git pull --rebase

if [ "$1" == "refresh" ]; then
    echo ""
    echo "Stage script refreshed. Run stage again without refresh option to stage latest changes"
    $SOURCE/../bin/stage-lock.sh release
    exit 0;
fi

echo ""
echo "Running composer"
composer install --no-dev --optimize-autoloader

echo ""
echo "Running npm"
npm install --production

echo ""
echo "Running bower"
node_modules/.bin/bower install --production

echo ""
echo "Snapping the database"
# Setup up temporary .my.cnf file
echo "[mysqldump]" > $HOME/.my.cnf
grep 'DB_USERNAME=' $PROD/.env | awk -F= '{print "user="$2}' >> $HOME/.my.cnf
grep 'DB_PASSWORD=' $PROD/.env | awk -F= '{print "password="$2}' >> $HOME/.my.cnf
echo "[mysql]" >> $HOME/.my.cnf
grep 'DB_USERNAME=' $DEST/.env | awk -F= '{print "user="$2}' >> $HOME/.my.cnf
grep 'DB_PASSWORD=' $DEST/.env | awk -F= '{print "password="$2}' >> $HOME/.my.cnf

$SOURCE/../bin/snap.sh
rm -f $HOME/.my.cnf

echo ""
echo "Copying file archive"
rsync -av --delete $PROD/storage/app/* $DEST/storage/app/

echo ""
echo "Syncing files"
rsync -av --delete --filter='protect .env' \
                   --filter='protect storage/framework/sessions/*' \
                   --filter='protect storage/framework/down' \
                   --filter='protect storage/logs/*' \
                   --filter='protect storage/app/*' \
                   --filter='protect public/error_log' \
                   --exclude='node_modules' \
                   --exclude='storage/debugbar' \
                   --exclude='tests' \
                   --exclude='.editorconfig' \
                   --exclude='.env.example' \
                   --exclude='.git*' \
                   --exclude='composer.*' \
                   --exclude='gulpfile.js' \
                   --exclude='package.json' \
                   --exclude='phpspec.yml' \
                   --exclude='phpunit.xml' \
                   --exclude='*.md' \
                   --exclude='bower.json' \
                   --exclude='.bowerrc' \
                   $SOURCE/ $DEST

echo ""
echo "Running migrations"
cd $DEST/
php artisan migrate

echo ""
echo "Fixing file permissions"
sudo chmod -R o+w $DEST/storage

$SOURCE/../bin/stage-lock.sh release
