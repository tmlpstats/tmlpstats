#!/usr/bin/env bash

set -o errexit
trap 'echo $0 Got error on line ${LINENO} ${$?}' ERR

# Ensure current working directory
MY_DIR=$(cd "$( dirname "${BASH_SOURCE[0]}" )" && cd .. && pwd)
cd "$MY_DIR"

echo "--- Installing applications and setting them up ---"

# VM is configured in host-only mode. If you decide to connect this machine to the internet,
# change these passwords since this file is shared publicly.
MYSQL_USER="root"
MYSQL_PASS="password"

DB_NAME="vagrant_dev_tmlpstats"
DB_USER="vagrant_dev_tmlp"
DB_PASS="SuperSecretCode!"


echo "--- Updating packages list ---"
sudo apt-get update > /dev/null 2>&1

echo "--- MySQL options ---"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password password $MYSQL_PASS"
sudo debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $MYSQL_PASS"

echo "--- Installing base packages ---"
sudo apt-get install -y git vim curl python-software-properties > /dev/null 2>&1
sudo add-apt-repository -y ppa:ondrej/php5-oldstable > /dev/null 2>&1

sudo apt-get update > /dev/null 2>&1
sudo apt-get install -y wget php5 apache2 php5-mcrypt mysql-server-5.5 php5-curl php5-mysql > /dev/null 2>&1

echo "--- Installing PHP-specific packages ---"
sudo apt-get install -y php5-xdebug > /dev/null 2>&1

cat << EOF | sudo tee -a /etc/php5/mods-available/xdebug.ini
xdebug.scream=1
xdebug.cli_colors=1
xdebug.show_local_vars=1
xdebug.remote_enable = 1
xdebug.remote_port = 9000
xdebug.remote_handler = "dbgp"
xdebug.remote_mode = req
xdebug.remote_connect_back = 1
xdebug.remote_log="/var/log/xdebug/xdebug.log"
EOF

sudo mkdir /var/log/xdebug
sudo chown www-data /var/log/xdebug

echo "--- Turn PHP errors on ---"
sudo sed -i "s/error_reporting = .*/error_reporting = E_ALL/" /etc/php5/apache2/php.ini
sudo sed -i "s/display_errors = .*/display_errors = On/" /etc/php5/apache2/php.ini



if [ ! -f /var/log/dbinstalled ];
then
    echo "--- Setup Datebase ---"
    echo "CREATE USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS'" | mysql -u$MYSQL_USER -p$MYSQL_PASS
    echo "CREATE DATABASE $DB_NAME" | mysql -u$MYSQL_USER -p$MYSQL_PASS
    echo "GRANT ALL ON $DB_NAME.* TO '$DB_USER'@'localhost'" | mysql -u$MYSQL_USER -p$MYSQL_PASS
    echo "flush privileges" | mysql -u$MYSQL_USER -p$MYSQL_PASS
    touch /var/log/dbinstalled
fi

echo "--- Setting up document root. Update as needed ---"
# if /var/www is not a symlink then create the symlink and set up apache
if [ ! -h /var/www/tmlpstats ];
then
    sudo ln -fs /vagrant/tmlpstats/public /var/www/tmlpstats > /dev/null 2>&1
    sudo a2enmod rewrite 2> /dev/null
    sed -i '/AllowOverride None/c AllowOverride All' /etc/apache2/sites-available/default
fi


# echo "--- Update apache vhosts ---"
# sudo echo '
# <VirtualHost *:80>
#     ServerName tmlp.vagrant-dev.com
#     DocumentRoot /var/www/tmlpstats/
#     <Directory /var/www/tmlpstats>
#         Options Indexes FollowSymLinks
#         AllowOverride All
#     </Directory>
# </VirtualHost>
# ' >> /etc/apache2/sites-available/default

echo "--- Restart apache ---"
sudo service apache2 restart > /dev/null 2>&1

echo "--- Setting Up Application ---"
echo "--- Write .ENV file ---"
echo "
APP_ENV=local
APP_DEBUG=true

DB_HOST=localhost
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASS

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=sync

MAIL_DRIVER=smtp
MAIL_HOST=localhost
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null

ADMIN_EMAIL=noreply@tmlpstats.com" > /vagrant/tmlpstats/.env

echo "--- Fake email service ---"
sudo sh -c "cat > /etc/init/fake-email.conf" <<EOF
description	"Debug Email"

start on runlevel [2345]
stop on runlevel [!2345]

respawn
respawn limit 10 5
umask 022
console none

script
	while [ ! -d /vagrant/tmlpstats ]; do
		echo "waiting for tmlpstats directory availability..."
		sleep 1
	done

	python -m smtpd -n -c DebuggingServer localhost:2525 | tee -a /vagrant/tmlpstats/email-output.log
end script
EOF
sudo start fake-email || true

echo "--- Run Composer Install ---"
curl -sS http://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

cd /vagrant/tmlpstats && composer install

echo "--- Run Laravel Migrations and Seeds ---";
cd /vagrant/tmlpstats && php artisan migrate:refresh --seed

echo "--- Setup complete ---"
echo ""
echo "Add VM's hostname to your hosts file:"
echo "  echo '192.168.56.102  vagrant-dev.com' | sudo tee -a /etc/hosts"
echo "Access the website in your browser:"
echo "  http://vagrant-dev.com/tmlpstats/"
