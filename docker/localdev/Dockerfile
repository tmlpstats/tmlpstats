FROM tmlpstats/builder

EXPOSE 3000

ENV DB_DATABASE=tmlpstats_main \
    DB_USERNAME=tmlpstats \
    DB_PASSWORD=doesntmatter \
    CACHE_DRIVER=memcached \
    SESSION_DRIVER=memcached \
    QUEUE_DRIVER=sync \
    MAIL_DRIVER=smtp \
    MAIL_HOST=localhost \
    MAIL_PORT=2525 \
    MAIL_USERNAME=null \
    MAIL_PASSWORD=null \
    ADMIN_EMAIL=your@email.com \
    MAIL_PRETEND=true \
    APP_KEY=4a2d4738b5d10e8b6b452d3eb60372d5 \
    APP_ENV=local \
    APP_DEBUG=true

COPY docker/localdev/000-default.conf /etc/apache2/sites-available
COPY docker/localdev/start.sh docker/localdev/localdev-version.txt /usr/bin/
CMD bash /usr/bin/start.sh
