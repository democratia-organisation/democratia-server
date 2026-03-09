# syntax=docker/dockerfile:1
FROM php:latest

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get update --yes && apt-get upgrade --yes \
    && apt-get install --yes git  \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    $PHPIZE_DEPS  \
    openssh-client \
    && cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    # Configuration complète de Xdebug
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /usr/src/server
COPY composer.json .
RUN composer install
COPY . .
CMD [ "php", "-S", "0.0.0.0:80"]
