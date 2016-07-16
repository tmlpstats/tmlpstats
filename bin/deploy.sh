#!/usr/bin/env bash

##
# Deploy website
#

set -e

SOURCE="$HOME/tmlpstats.git/src"
DEST="/var/www/tmlpstats.com"
ROLLBACK="$HOME/tmlpstats.rollback"

cd $DEST/
php artisan down

if [ "$1" == "rollback" ]; then
    rsync -av --delete --filter='protect storage/framework/down' $ROLLBACK/ $DEST
    php artisan up
    exit 0;
elif [[ "$1" != "" ]]; then
    # Convenience, assume the user wanted to do a fast-deploy of an old version.
    # So we stage the version and follow up with a deploy of the version.
    echo "Going to stage the revision $1 before deploying..."
    $SOURCE/../bin/stage.sh "$1"
    read -p "Stage complete. Are you sure you want to fast-deploy this revision? [y/N]" ANSWER
    if [[ "$ANSWER" != "y" && "$ANSWER" != "Y" ]]; then
        echo "aborting."
        exit 1
    fi
fi


# Do not mark releases when we're fast deploying a different release.
# This helps avoid creating tons of tags pointing to the same thing
if [[ "$1" != release* ]]; then
    # Mark release with a tag for easy swapping between releases.
    cd "$SOURCE"
    # We don't expect to do multiple deploys a minute, unlike staging, so we can do one tag per minute
    RELEASE_TAG="release-$(date +"%Y%m%d-%H%M")"
    git tag "$RELEASE_TAG"
    # We push to a remote named 'release' - this allows us a separated config or even repo if needed
    git push release "$RELEASE_TAG"
fi

# Setup rollback copy
echo ""
echo "Backing up for rollback"
rsync -aq --delete --exclude='storage/framework/down' \
                   --exclude='storage/framework/views' \
                   $DEST/ $ROLLBACK

echo ""
echo "Syncing files"
rsync -av --delete --filter='protect .env' \
                   --filter='protect storage/framework/sessions/*' \
                   --filter='protect storage/framework/down' \
                   --filter='protect storage/logs/*' \
                   --filter='protect storage/app/*' \
                   --filter='protect public/error_log' \
                   --filter='protect public/build/**' \
                   --exclude='bower_components' \
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
                   --exclude='.babelrc' \
                   $SOURCE/ $DEST

echo ""
echo "Running migrations"
cd $DEST/
php artisan migrate

echo ""
echo "Flushing Reports Cache"
cd $DEST/
php artisan cache:clear-tag reports
php artisan cache:clear-tag api

echo ""
echo "Fixing file permissions"
sudo chmod -R o+w $DEST/storage

php artisan up
