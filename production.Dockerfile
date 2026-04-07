# syntax=docker/dockerfile:1

FROM php:8.2-apache@sha256:d21cb9d7b71bffd3ce7b10cb88015ae1f2b5851335b53c6aeba6aa73380d1ac2

LABEL com.democratia.server="1.0.0"

RUN apt-get update && apt-get install --yes --no-install-recommends \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libzip-dev \
    unzip
    zip

RUN groupadd -r common_user && useradd --no-log-init -r -g common_user koyok
USER koyok

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip opcache
RUN a2enmod rewrite
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html
RUN echo "FallbackResource /rest.php" > .htaccess

COPY composer.* .
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .
RUN rm -rf .husky
