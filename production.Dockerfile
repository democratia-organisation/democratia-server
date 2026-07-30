# syntax=docker/dockerfile:1

FROM php:8.2-fpm-bullseye

LABEL com.democratia.server="1.0.0"

RUN apt-get update && apt-get install --yes --no-install-recommends \
    libfreetype6-dev \
    libjpeg-dev \
    libpng-dev \
    libzip-dev \
    unzip \
    zip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip opcache

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN groupadd -r common_user && useradd --no-log-init -r -g common_user koyok

USER koyok

WORKDIR /var/www/html

COPY composer.* .
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .
RUN rm -rf .husky .vscode

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]