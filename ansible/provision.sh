#!/bin/bash

if [[ -d /vagrant-src && ! -d /vagrant ]]; then
    echo "Copying vagrant-src to vagrant...."
    rsync -ruv --exclude '.vagrant' /vagrant-src /vagrant
    chown -R vagrant:vagrant /vagrant || true
fi

echo ""
echo -n "Checking for ansible...."
if ! which ansible; then
	echo "Not Found. Installing Ansible..."
	sudo apt-get update
	sudo apt-get install --no-install-recommends -y \
			build-essential python-pip python-dev python-pycurl python-mysqldb libffi-dev libssl-dev
	sudo pip install markupsafe ansible
fi

sudo mkdir -p /etc/ansible
printf '[vagrant]\nlocalhost\n' | sudo tee /etc/ansible/hosts > /dev/null

echo "Running provisioner: ansible"
PYTHONUNBUFFERED=1 ansible-playbook -c local /vagrant/ansible/playbook.yml
