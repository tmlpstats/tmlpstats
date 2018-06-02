#!/bin/bash

set -e

FILE="tmlpstats.tar.gz"
OUTPUT_FILE="/opt/website/${FILE}"
export GIT_BRANCH="${1:-master}"

CHECKOUT=$(cd $(dirname "$0"); cd ../..; pwd)

cd $CHECKOUT

$CHECKOUT/deployment/prod-packager/fetch-and-clean-git.sh "$GIT_BRANCH"

docker stop builder || true
docker rm builder || true


if [[ -z "$NO_REBUILD_BUILDER" ]]; then
	# This should only need to rebuild if node modules and such change.
	docker build -t tmlpstats/builder -f Dockerfile.builder .
fi

docker run -ti                         \
           --rm                        \
           --name builder              \
           -v $CHECKOUT:/opt/tmlpstats \
           -e GIT_BRANCH="$GIT_BRANCH" \
           tmlpstats/builder /bin/bash -c /opt/tmlpstats/deployment/prod-packager/make-package.sh

mv -f ${FILE} ${OUTPUT_FILE}
