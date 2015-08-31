# TMLP Statistics Web Application

## Installing:
This repo includes a Vagrant configuration to make development easier.

### Clone Repo
```
$ mkdir ~/dev
$ cd ~/dev
$ git clone <repo url>
```

### Setup for Database Seeding
If you have an export of the database, the provisioning will seed the database for you. Generate a CSV export
of the database, and copy the files into `~/dev/export/`. Note, the export should be a collection of files named
after the table they contain.
You can also snap the database after provisioning using Laravel's artisan migrate command:
```
$ cd /vagrant/tmlpstats
$ php artisan migrate:refresh --seed

```

### Start Your dev VM
```
$ cd ~/dev
$ vagrant up
```
Wait for the scripts to complete. This will take a while the first time since Vagrant has to download the base image.

Your VM should now be ready to go.

You can add an entry to your hosts file for ease of testing:
```
echo '192.168.56.102  vagrant-dev.com' | sudo tee -a /etc/hosts"
```

View the application in your browser. Visit: `http://vagrant-dev.com/tmlpstats/`