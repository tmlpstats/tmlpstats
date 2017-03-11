#!/bin/bash
set -e

OPTIONS_DIR=/app/src/.localdev

if mkdir "$OPTIONS_DIR" 2>/dev/null; then
    cat > "$OPTIONS_DIR/help.txt" <<EOF
This directory allows you to set options by creating small files in here.
Valid option files:

- norefresh: If exists, don't keep refreshing composer/bower/npm.
- watch: If exists, use 'gulp watch' to refresh files. Might have performance concerns.
- nogulp: Rarely used, but sets up in case you want to run gulp on your own machine.
EOF
fi

composer_rerun() {
    pushd /app/src
    composer install --no-scripts
    composer install --no-autoloader
    popd
}


if [[ ! -f "$OPTIONS_DIR/norefresh" && -d /app/.git ]]; then
    # Take the git hash and the hashes of the important files.
    # If any change, re-run composer/bower.

    HASHES=$(md5sum composer.json composer.lock)
    VENDORHASHES=/app/src/vendor/.hashes
    if [[ ! -f "$VENDORHASHES" || "$(cat $VENDORHASHES)" != "$HASHES" ]]; then
        echo "Overwriting vendor folder $(du -sh vendor)"
        if [ ! -d /app/src/vendor ]; then
            mkdir /app/src/vendor
        else
            rm -rf /app/src/vendor/*
        fi
        cp -r vendor/* /app/src/vendor
        composer_rerun
        echo "$HASHES" > "$VENDORHASHES"
    else
        echo "No changes detected...skipping composer."
    fi

    HASHES=$(md5sum package.json bower.json)
    REFRESH=$(cat hashes.install || echo "bork")
    if [[ "$HASHES" != "$REFRESH" ]]; then
        bower install --allow-root
        npm install
    fi
fi

# If we get here, we got a vendor folder but no autoloads.
if [ ! -f /app/src/vendor/composer/autoload_real.php ]; then
    composer_rerun
fi

# It seems like webpack resolves via the symlink and then can't find node_modules.
# We can simply symlink them in reverse. (this works on windows!)
if [ ! -d /app/src/node_modules ]; then
    echo "!! Making node_modules symlink"
    ln -s $PWD/node_modules /app/src/node_modules
fi

# Unfortunately, gulpfile does not work properly as a symlink.
# Copy it in when it's time to use it.
if [ -f /app/src/gulpfile.js ]; then
    rm -f gulpfile.js
    cp /app/src/gulpfile.js .
fi

if [ -f $OPTIONS_DIR/watch ]; then
    apache2-foreground &
    BROWSERSYNC_TARGET=localhost gulp watch
else
    if [ ! -f $OPTIONS_DIR/nogulp ]; then
        gulp
    fi
    apache2-foreground
fi
