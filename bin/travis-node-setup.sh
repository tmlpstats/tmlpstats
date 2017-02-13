#!/bin/bash

set -e

if which nvm; then
    nvm install 6;
else
    for progname in npm node nvm; do
        if which "$progname"; then
            rm -f $(which "$progname")
        fi
    done
    echo "Installing node 6...."
    curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash - > /dev/null
    sudo apt-get install -y nodejs;
fi
which node
node -v
#npm install npm;
npm install
