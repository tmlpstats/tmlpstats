#!/bin/bash

FARMDIR=${1}/src
APPDIR=${2}/src

set -o errexit

mkdir -p "$APPDIR"

cd "$FARMDIR"
mkdir -p storage/{app,framework/logs}

LINKFILES=(
    app config database public tests resources
    artisan
    webpack.config.js server.php gulpfile.js .env
)

for subdir in "${LINKFILES[@]}";
do
    echo "Linking ${APPDIR}/$subdir"
    ln -sf "${APPDIR}/$subdir"
done

TRASHFILES=(
    composer.json composer.lock package.json
)

for trashfile in "${TRASHFILES[@]}";
do
    ln -sf "${APPDIR}/$trashfile"
done
