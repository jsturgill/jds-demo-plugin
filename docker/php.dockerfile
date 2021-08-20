FROM php:7.4-fpm-alpine

ADD ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN addgroup -g 1000 wordpress && adduser -G wordpress -g wordpress -s /bin/sh -D wordpress

RUN mkdir -p /var/www/html

RUN chown wordpress:wordpress /var/www/html

WORKDIR /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql
