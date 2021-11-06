# latest tag
FROM npulidom/alpine-phalcon

# install any other package...
RUN apk add --no-cache php-pdo_mysql && rm -rf /var/cache/apk/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer



# working directory
WORKDIR /var/www/public

# composer install dependencies
COPY composer.json .
RUN composer install --no-dev && composer dump-autoload --optimize --no-dev

# project code
#COPY . .

# start supervisor
CMD ["--nginx-env"]
