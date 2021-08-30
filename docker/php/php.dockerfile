# https://github.com/docker-library/php/tree/496cd2fea4cedb5eb78f9b3cbbb342aa7afb0c68/7.4
FROM php:7.4-fpm-alpine
ARG dbname
ARG dbuser
ARG dbpass
ARG dbhost
ARG url
ARG title
ARG admin_user
ARG admin_password
ARG admin_email

ENV DBNAME=$dbname
ENV DBUSER=$dbuser
ENV DBPASS=$dbpass
ENV DBHOST=$dbhost
ENV URL=$url
ENV TITLE=$title
ENV ADMIN_USER=$admin_user
ENV ADMIN_PASSWORD=$admin_password
ENV ADMIN_EMAIL=$admin_email

# see https://github.com/wp-cli/wp-cli/issues/3840
ENV PAGER=more

ADD ./docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

RUN addgroup -g 1000 wordpress && adduser -G wordpress -g wordpress -s /bin/sh -D wordpress

RUN mkdir -p /var/www/html/wordpress

RUN chown -R wordpress:wordpress /var/www/html

WORKDIR /var/www/html

RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable pdo_mysql

# mariadb-connector-c adds caching_sha2_password see https://github.com/craftcms/docker/issues/19
# and bash is added for wait-for-it.sh
RUN apk add mysql-client mariadb-connector-c bash

RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp \
    && chmod +x /usr/local/bin/wp

COPY ./docker/scripts /var/scripts

COPY ./docker/files/wordpress /var/www/html

CMD sh -c "/var/scripts/init.sh"