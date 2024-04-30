FROM php:8.1-fpm-buster
RUN apt-get update \
  && apt-get install -y build-essential zlib1g-dev default-mysql-client curl gnupg procps vim git unzip libzip-dev libpq-dev libicu-dev libonig-dev libpng-dev libjpeg-dev libfreetype6-dev

RUN docker-php-ext-install mysqli pdo pdo_mysql intl zip gd bcmath

ADD ./docker/development/php/www.conf /usr/local/etc/php-fpm.d/

RUN mkdir -p /var/www/html

RUN addgroup --gid 1000 laravel && adduser --ingroup laravel --uid 1000 --shell /bin/sh --disabled-password --gecos "" laravel
RUN chown laravel:laravel /var/www/html

WORKDIR /var/www/html

USER laravel

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
