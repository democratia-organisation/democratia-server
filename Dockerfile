FROM php:latest

RUN apt-get update --yes && apt-get upgrade --yes \
    && apt-get install git $PHPIZE_DEPS --yes \
    && cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo pdo_mysql \
    && echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.log=/tmp/xdebug.log" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY . /usr/src/api
WORKDIR /usr/src/api
CMD ["php", "-S", "0.0.0.0:80", "./rest.php"]