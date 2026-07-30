# syntax=docker/dockerfile:1

FROM php:8.5-fpm@sha256:0dc450d0a0e81ba501973b8e303f5d45af2ed989e08730f597d8fc07fb289efd

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
RUN rm -rf .husky .vscode package.json bun.lockb

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000

CMD ["php-fpm"]