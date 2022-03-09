# latest tag
FROM npulidom/alpine-phalcon

# install any other package...
RUN apk add --no-cache php-pdo_mysql && rm -rf /var/cache/apk/*
RUN apk add --no-cache php-xmlwriter && rm -rf /var/cache/apk/*

# working directory
WORKDIR /var/www/public

# project code
COPY . .
# php ini
COPY ./config/php.ini /etc/php7/

# start supervisor
CMD ["--nginx-env"]
