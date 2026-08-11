FROM php:8-fpm-alpine@sha256:41848df84031c4fe6e898f79461ec62edc4c7008e9a5e361e552859b0482b9d7

LABEL com.democratia.server="1.0.0"

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql gd zip opcache
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.* .
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .
RUN rm -rf .husky .vscode package.json bun.lock

RUN mkdir -p /usr/local/etc/php-fpm.d/ && cp ./config/www.production.conf /usr/local/etc/php-fpm.d/www.conf \
    && rm -rf ./config

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000

CMD ["php-fpm"]