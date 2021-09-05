FROM php:7.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends gnupg git-core jq unzip \
    nano zip libjpeg-dev libpng-dev libpq-dev libsqlite3-dev libwebp-dev libzip-dev && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure zip --with-zip && \
    docker-php-ext-configure gd --with-jpeg --with-webp && \
    docker-php-ext-install exif gd mysqli pdo pdo_mysql zip && \
    curl -sL https://deb.nodesource.com/setup_12.x | bash - && \
    apt-get update && \
    apt-get install -y --no-install-recommends nodejs && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && \
    apt-get install -y --no-install-recommends yarn && \
    npm install -g npm && \
    apt update && apt install -y htop && \
    yes | pecl install xdebug && \
    echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/xdebug.ini && \
    docker-php-ext-enable xdebug

RUN { \
    echo 'log_errors=on'; \
    echo 'display_errors=off'; \
    echo 'upload_max_filesize=32M'; \
    echo 'post_max_size=32M'; \
    echo 'max_execution_time=300'; \
    echo 'memory_limit=128M'; \
  } > /usr/local/etc/php/conf.d/docker-oc-php.ini

COPY --chown=www-data:www-data ./ /var/www/html
COPY docker-oc-entrypoint /usr/local/bin/

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
  composer install \
  --ignore-platform-reqs \
  --optimize-autoloader \
  --no-interaction \
  --no-progress
RUN npm --prefix ./themes/demo install && \
  npm --prefix ./themes/demo run production

RUN a2enmod rewrite

# Add application
WORKDIR /var/www/html

RUN echo 'exec php artisan "$@"' > /usr/local/bin/artisan && \
  echo 'exec php artisan tinker' > /usr/local/bin/tinker && \
  echo '[ $# -eq 0 ] && exec php artisan october || exec php artisan october:"$@"' > /usr/local/bin/october && \
  sed -i '1s;^;#!/bin/bash\n[ "$PWD" != "/var/www/html" ] \&\& echo " - Helper must be run from /var/www/html" \&\& exit 1\n;' /usr/local/bin/artisan /usr/local/bin/tinker /usr/local/bin/october && \
  chmod +x /usr/local/bin/artisan /usr/local/bin/tinker /usr/local/bin/october /usr/local/bin/docker-oc-entrypoint

ENTRYPOINT ["docker-oc-entrypoint"]

CMD ["apache2-foreground"]
