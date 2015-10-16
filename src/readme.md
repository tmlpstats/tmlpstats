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

If you need to see paths or default passwords, check inside the `bin/bootstrap.sh`.

Your VM should now be ready to go.

You can add an entry to your hosts file for ease of testing:
```
echo '192.168.56.102  vagrant-dev.com' | sudo tee -a /etc/hosts"
```

View the application in your browser. Visit: `http://vagrant-dev.com/tmlpstats/`

### Setup for Database Seeding
If you have an export of the database, the provisioning will seed the database for you. Generate a CSV export of the database,
and copy the files into `~/dev/tmlpstats/export/`. Note, the export should be a collection of files named after the table
they contain.

You can also snap the database after provisioning using Laravel's artisan migrate command:
```
$ vagrant ssh
<inside vagrant VM>
$ cd /vagrant/tmlpstats
$ php artisan migrate:refresh --seed
```

Seeding will setup a default admin account you can use to login locally if you don't have a live account.
