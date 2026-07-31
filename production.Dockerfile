FROM php:8-fpm-alpine@sha256:79def1d16ece3ab1a6656c46a23bfd80ad33887fbd33626e7bd743cef54ef9c6

LABEL com.democratia.server="1.0.0"

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_mysql gd zip opcache
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN addgroup -S common_user && adduser -S -G common_user koyok

WORKDIR /var/www/html

COPY composer.* .
RUN composer install --no-dev --optimize-autoloader --no-interaction

COPY . .
RUN rm -rf .husky .vscode package.json bun.lockb

RUN mkdir -p /usr/local/etc/php-fpm.d/ && cp ./config/www.production.conf /usr/local/etc/php-fpm.d/www.conf \
    && rm -rf ./config

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000

CMD ["php-fpm"]