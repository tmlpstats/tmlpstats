#!/bin/bash

set -e
if [[ "$(docker-compose ps | egrep 'tmlpstats_local.*Up')" = "" ]]; then
    echo "Starting local backgrounded"
    LOCALDEV_WATCH=n docker-compose up -d local || true
else
	echo "Skipping starting local..."
fi

cd "$(dirname $0)/../src"
npm run hot