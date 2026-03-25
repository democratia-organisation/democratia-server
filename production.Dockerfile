# syntax=docker/dockerfile:1

FROM php:8.2-apache

USER user

RUN apt-get update && apt-get install --yes --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip opcache \
    && a2enmod rewrite \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json .
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .
RUN rm -rf .husky
RUN chown -R www-data:www-data /var/www/html \
    && echo "FallbackResource /rest.php" > .htaccess
