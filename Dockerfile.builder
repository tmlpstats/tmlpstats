FROM php:7.0-apache

RUN apt-get update && apt-get install -y \
    --no-install-recommends \
    libmcrypt-dev                        \
    libmemcached-dev                     \
    libz-dev                             \
    libxml2-dev                          \
    zlib1g-dev                           \
    libnotify-bin                        \
    npm                                  \
    git                                  \
    wget                                 \
 && docker-php-ext-install -j$(nproc)    \
    mcrypt                               \
    pdo_mysql                            \
    xml                                  \
    zip                                  \
 && git clone --branch php7 https://github.com/php-memcached-dev/php-memcached /usr/src/php/ext/memcached \
 && cd /usr/src/php/ext/memcached        \
 && docker-php-ext-configure memcached   \
 && docker-php-ext-install memcached     \
 && apt-get autoremove -y                \
 && apt-get clean                        \
 && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN wget https://getcomposer.org/installer -O /tmp/composer-installer.php \
 && php /tmp/composer-installer.php --install-dir=/bin --filename=composer \
 && composer self-update

# Install NPM and Node
RUN /usr/bin/npm install -g n              \
 && /usr/local/bin/n 7                     \
 && /usr/local/bin/npm install -g npm      \
 && /usr/local/bin/npm install -g bower

RUN mkdir -p /var/www/tmlpstats/src && \
    a2enmod rewrite
WORKDIR /var/www/tmlpstats/src 

##### Variant processes - Bake in as much as we can, make the module install much smaller later

# Composer, Bower
ADD src/composer.json src/composer.lock src/bower.json /var/www/tmlpstats/src/
RUN composer install --no-autoloader --no-scripts && \
    md5sum composer.json composer.lock > vendor/.hashes && \
    bower install --allow-root && \
    md5sum bower.json > bower_components/.hashes

# Node Modules
ADD src/package.json src/package-lock.json /var/www/tmlpstats/src/
RUN npm --progress false install && \
    md5sum package.json package-lock.json > node_modules/.hashes


ADD docker/builder/symlink-farm.sh /tmp/
RUN bash /tmp/symlink-farm.sh /var/www/tmlpstats /app