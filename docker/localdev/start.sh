#!/bin/bash
set -e
FARM_DIR=/var/www/tmlpstats/src
OPTIONS_DIR=/app/src/.localdev

VCHECK_FILE="/app/docker/localdev/localdev-version.txt"
if [[ ! -f "$VCHECK_FILE" || "$(cat $VCHECK_FILE)" != "$(cat /usr/bin/localdev-version.txt)" ]]; then
    cat <<EOF
WARNING!
Container version does not match expected container major version.
This means you need a rebuild!

Windows users: run rebuild.cmd from the bin folder
Mac/Linux users: bin/rebuild.sh
EOF
    exit 1
fi


if mkdir "$OPTIONS_DIR" 2>/dev/null; then
    cat > "$OPTIONS_DIR/help.txt" <<EOF
This directory allows you to set options by creating small files in here.
Valid option files:

- norefresh: If exists, don't keep refreshing composer/npm.
- watch: If exists, use 'npm run watch' to refresh files. Might have performance concerns.
- nojs: Rarely used, but sets up in case you don't want to blow away JS files.
EOF
fi

cd /app/src

composer_rerun() {
    rm -f bootstrap/cache/* || true
    composer install --no-scripts
    composer install --no-autoloader
}

# Shortcut to compare the hash file (if exists)
# Param $1: the potential hashfile
# Param $2: the string content to compare with (usually an md5sum)
# Returns: success(code=0) if hash file both exists and equals the target
_comparehashfile() {
    [[ -f "$1" && "$(cat "$1")" = "$2" ]]
}

hashcheck() {
    local hashesfile="$1/.hashes"
    local tohash="$2"
    local handler="$3"
    local pipeline_name="$4"
    # don't quote $tohash to let it sum multiple files
    local HASHES=$(md5sum $tohash)
    # Running the pipeline as the last statement makes this the return value
    if ! _comparehashfile "$hashesfile" "$HASHES"; then
        echo "Hashes don't match... rebuilding ${pipeline_name}"
        $handler "$HASHES"
        echo "> Done, updating hashes file"
        echo "$HASHES" > "$hashesfile"
    else
        echo "No changes detected... skipping ${pipeline_name}"
    fi
}

_rebuild_composer() {
    local HASHES="$1"
    supposed_vendor="/var/www/tmlpstats/src/vendor"
    if _comparehashfile "${supposed_vendor}/.hashes" "$HASHES"; then
        echo "Overwriting vendor folder $(du -sh "$supposed_vendor")"
        if [[ ! -d /app/src/vendor ]]; then
            mkdir /app/src/vendor
        else
            rm -rf /app/src/vendor/*
        fi
        cp -r "$supposed_vendor"/* /app/src/vendor
    fi
    echo "Re-running composer build"
    composer_rerun
}


_rebuild_npm() {
    if [ ! -d /app/src/node_modules ]; then
        mkdir  /app/src/node_modules || true
    fi
    farmnode="${FARM_DIR}/node_modules"
    if _comparehashfile "${farmnode}/.hashes" "$1"; then
        echo "Taking the shortcut of copying node_modules instead"
        rm -rf /app/src/node_modules || true
        cp -r "${farmnode}" /app/src/node_modules
    else
        echo "Doing an npm install"
    fi
    npm install
}

_rebuild_bower() {
    bower install --allow-root
}



if [[ ! -f "$OPTIONS_DIR/norefresh" && -d /app/.git ]]; then
    # Take the git hash and the hashes of the important files.
    # If any change, re-run composer/bower.
    hashcheck vendor "composer.json composer.lock" _rebuild_composer "composer" 

    # hashcheck bower_components bower.json _rebuild_bower "bower"

    hashcheck node_modules "package.json package-lock.json" _rebuild_npm "npm"
fi

if [[ -d node_modules/node-sass && ! -d node_modules/node-sass/vendor/linux-x64-51 ]]; then
    echo "Rebuilding node-sass...."
    npm rebuild node-sass
fi

# If we get here, we got a vendor folder but no autoloads.
if [ ! -f /app/src/vendor/composer/autoload_real.php ]; then
    composer_rerun
fi

export IN_LOCALDEV=y
if [[ "$LOCALDEV_WATCH" = "hot" ]]; then
    apache2-foreground &
    IN_LOCALDEV_WATCH=y npm run hot
elif [[ "$LOCALDEV_WATCH" = "y" || -f $OPTIONS_DIR/watch ]]; then
    apache2-foreground &
    IN_LOCALDEV_WATCH=y npm run watch
else
    if [ ! -f $OPTIONS_DIR/nojs ]; then
        npm run dev
    fi
    apache2-foreground
fi
