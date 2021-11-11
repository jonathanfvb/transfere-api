# latest tag
FROM npulidom/alpine-phalcon

# install any other package...
RUN apk add --no-cache php-pdo_mysql && rm -rf /var/cache/apk/*
RUN apk add --no-cache php-xmlwriter && rm -rf /var/cache/apk/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# working directory
WORKDIR /var/www/public

# composer install dependencies
COPY composer.json .
COPY composer.lock .
RUN composer install && composer dump-autoload

# project code
COPY . .
# php ini
COPY ./config/php.ini /etc/php7/

# start supervisor
CMD ["--nginx-env"]
