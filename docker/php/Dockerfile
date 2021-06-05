FROM php:8.0-fpm-alpine3.13

RUN apk update && apk upgrade
RUN apk add --no-cache --repository https://alpine.global.ssl.fastly.net/alpine/edge/community/

RUN apk add --no-cache curl-dev icu-dev libzip-dev
RUN docker-php-ext-install mysqli pdo pdo_mysql intl zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ADD ./docker/php/www.conf /usr/local/etc/php-fpm.d/

RUN addgroup -g 1000 laravel && adduser -G laravel -g laravel -s /bin/sh -D laravel

RUN mkdir -p /var/www/html

RUN chown laravel:laravel /var/www/html

WORKDIR /var/www/html

RUN docker-php-ext-install pdo pdo_mysql
