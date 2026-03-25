# syntax=docker/dockerfile:1
FROM node:alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install


FROM php:latest

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV PATH="${PATH}:/root/.composer/vendor/bin"

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
    wget
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN wget https://github.com/php/pie/releases/latest/download/pie.phar  
RUN chmod +x pie.phar  
RUN mv pie.phar /usr/local/bin/pie
RUN pie install xdebug/xdebug
RUN pie install osmanov/pecl-ev
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    # Configuration de Xdebug
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /usr/src/server
COPY --from=node_builder /app ./node_modules
COPY composer.json .
RUN composer install
COPY . .
CMD [ "php", "-S", "0.0.0.0:80"]
