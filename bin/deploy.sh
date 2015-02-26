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

PHP=/ramdisk/php/54/bin/php54-cli

SOURCE='tmlpstats'
DEST='../public_html/tmlpstats'
ROLLBACK='rollback'

if [ "$1" == "rollback" ]; then

    rm -rf $DEST/*
    cp -a $ROLLBACK/* $DEST/
    exit 0;
fi

rm -rf $ROLLBACK
mkdir -p $ROLLBACK
cp -a $DEST/* $ROLLBACK/

cd $SOURCE/
$PHP ~/common/composer.phar update
cd ../

rm -rf $DEST/*
rsync -av --exclude='.git*' --exclude='readme.md' --exclude'bin' --exclude='composer.*' $SOURCE/ $DEST