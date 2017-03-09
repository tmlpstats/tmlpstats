#!/bin/bash

FARMDIR=${1}/src
APPDIR=${2}/src

mkdir -p "$APPDIR"

cd "$FARMDIR"
mkdir -p storage/{app,framework/logs}

LINKFILES=(
	app config database public tests 
	artisan
	webpack.config.js server.php gulpfile.js .env
)

for subdir in "${LINKFILES[@]}";
do
	ln -sf "${APPDIR}/$subdir"
done