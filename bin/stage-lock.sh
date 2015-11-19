#!/usr/bin/env bash

lockdir=/var/lock/stage-lock
pidfile=/var/lock/stage-lock/pid

usage() {
    echo "Usage: $0 [release]";
    echo "  Lock or release stage lock";
    echo "";
}

if [ $# = 1 ] && [ "$1" = "release" ]; then
    rm -rf $lockdir
    exit 0;
elif [ $# != 0 ]; then
    usage
    exit 1;
fi

if ( mkdir ${lockdir} ) 2> /dev/null; then
    echo $$ > $pidfile
else
    echo "Stage lock exists: $lockdir owned by $(cat $pidfile)"
    exit 1;
fi
