#!/bin/bash

set -e

export N_PREFIX=$HOME/node  
export PATH=$N_PREFIX/bin:$PATH

if which nvm; then
    nvm install 6;
else
    for progname in npm node nvm; do
        if which "$progname"; then
            rm -f $(which "$progname")
        fi
    done
    echo "Installing node 10...."
    curl -L https://git.io/n-install | bash -s -- -y
    $N_PREFIX/bin/n 10
    $N_PREFIX/bin/npm install -g npm
fi
node -v
#npm install npm;
sudo apt-get install g++ build-essential
npm install

cd /usr/local/bin
sudo ln -s $N_PREFIX/bin/node
sudo ln -s $N_PREFIX/bin/npm