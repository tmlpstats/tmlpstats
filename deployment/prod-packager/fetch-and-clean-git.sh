#!/bin/bash

#######################
# Fetch and Clean Git
#
# This script is separated from make-package since it runs _outside_ the container.
# It also allows us to avoid issues that happen when a git checkout changes a shell script.

set -e

GIT_BRANCH="${1:-$GIT_BRANCH}"

git fetch
git checkout master
git reset --hard
if [ "${GIT_BRANCH}" != "master" ]; then
    git branch -D "${GIT_BRANCH}" || true
    git checkout "${GIT_BRANCH}"
else
    git reset --hard HEAD && git pull
fi