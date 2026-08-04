FROM php:8-fpm-alpine@sha256:9690c7464f2d5f2acfab2822f0aa757994460c3edf737d5710fbcab974ea8459

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
RUN rm -rf .husky .vscode package.json bun.lock

RUN mkdir -p /usr/local/etc/php-fpm.d/ && cp ./config/www.production.conf /usr/local/etc/php-fpm.d/www.conf \
    && rm -rf ./config

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 9000

CMD ["php-fpm"]