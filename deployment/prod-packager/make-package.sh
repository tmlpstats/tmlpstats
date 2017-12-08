#!/bin/bash -x

###########################################################
# Make Package
# 
# This script builds a packaged output from a git checkout,
# by fetching/updating from git, ensuring a clean run, and
# then running npm/composer installs, and finally doing
# a production JS compile(webpack). 
# 
# The final output is then spit out as a tarball so that we
# can then plop that into the prod container.

set -e

BASEDIR=/opt/tmlpstats
COMPOSER=$(which composer)
ARCHIVE_FILE="${BASEDIR}/tmlpstats.tar.gz"
GIT_BRANCH=${BRANCH}


cd ${BASEDIR}/src

rm -f bootstrap/cache/*
${COMPOSER} install --optimize-autoloader --no-dev
npm --progress false install
if [[ -f bower.json && -f node_modules/.bin/bower ]]; then
    node_modules/.bin/bower install --production --allow-root
fi

######### ASSET BUILDING
rm -rf -- buildbackup
if [ -d public/build ]; then
    if [ -n "$ASSET_BACKUP" ]; then
        mv public/build buildbackup
    else
        rm -rf -- public/build
    fi
fi

npm run production

if [ -d buildbackup ]; then
    for suffix in js css; 
    do
        BACKUPDIR="buildbackup/${suffix}"
        # Step 1: Remove any files with same name as existing ones, to be careful.
        for f in $(ls public/build/${suffix});
        do
            rm -f -- "${BACKUPDIR}/${f}"
        done

        # Step 2: remove extras if too many
        NBACKUP=$(ls $BACKUPDIR | wc -l)
        if [[ $NBACKUP -gt 7 ]]; then
            ls $BACKUPDIR | tail -n +7 | xargs rm --
        fi

        # Step 3: Move the remaining stuff back.
        if [[ $NBACKUP -ne 0 ]]; then
            mv "${BACKUPDIR}"/* public/build/${suffix}/ || true
        fi
    done
fi

tar -cf ${ARCHIVE_FILE} \
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
    *
