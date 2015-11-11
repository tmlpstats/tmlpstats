# TMLP Statistics Web Application

## Installing:
This repo includes a Vagrant configuration to make development easier. These instructions are written for OSX/Linux. You can find
instructions for setting up and installing on windows on google.

Please read all of these instructions first.

### Clone Repo
```
$ cd ~/dev
$ git clone https://github.com/pdarg/tmlpstats
```

### Download and install Vagrant
Vagrant uses VirtualBox to run its virtual machines. Download and install VirtualBox:
`https://www.virtualbox.org/`

After installing VirtualBox, download and install vagrant:
`https://www.vagrantup.com/`

### Start Your dev VM
Now that vagrant is installed, from the command line, change directory into the git repo you cloned earlier and run vagrant up.
```
$ cd ~/dev/tmlpstats
$ vagrant up
```
Wait for the scripts to complete. This will take a while the first time since Vagrant has to download the base image. The scripts
will download and install all of the packages needed to run the TMLP Stats project locally.

If you need to see paths or default passwords, check inside the `ansible/playbook.yml`.

Your VM should now be ready to go.

You can add an entry to your hosts file for ease of testing:
```
$ echo '192.168.56.102  vagrant.tmlpstats.com' | sudo tee -a /etc/hosts"
```

View the application in your browser. Visit: `http://vagrant.tmlpstats.com/`

If you see a web page with a message about setting your hosts file, double check that there is an entry for the vagrant.tmlpstats.com domain.

### Setup for Database Seeding
If you have an export of the database, the provisioning will import the database for you. Grab the latest dev export and copy the file
to `~/dev/tmlpstats/export/tmlpstats_vagrant_export.sql`.

You can also snap the database after provisioning using the mysql import:
```
$ vagrant ssh
<inside vagrant VM>
$ mysql -u root vagrant_dev_tmlpstats < /vagrant/export/tmlpstats_vagrant_export.sql
```

Provisioning should setup a default admin account so you can login if you don't already have an account setup. You can
also run this after provisioning using Laravel's artisan migrate command:
```
$ vagrant ssh
<inside vagrant VM>
$ cd /vagrant/src
$ php artisan db:seed --class=DefaultAdminSeeder
```

See the DefaultAdminSeeder source code for the credentials.
