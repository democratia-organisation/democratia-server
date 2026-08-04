# syntax=docker/dockerfile:1

FROM oven/bun:alpine AS bun_builder
WORKDIR /app
COPY package*.json ./
RUN bun install

FROM php:8.5-fpm

COPY --from=oven/bun:latest /usr/local/bin/bun /usr/local/bin/bun
COPY --from=oven/bun:latest /usr/local/bin/bunx /usr/local/bin/bunx
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
COPY --from=node:latest /usr/local/bin/node /usr/local/bin/node

ENV PATH="${PATH}:/root/.composer/vendor/bin"

RUN apt-get update && apt-get install --yes --no-install-recommends \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    $PHPIZE_DEPS \
    openssh-client \
    wget \
    && rm -rf /var/lib/apt/lists/*

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

RUN wget https://github.com/php/pie/releases/latest/download/pie.phar \
    && chmod +x pie.phar \
    && mv pie.phar /usr/local/bin/pie

RUN pie install xdebug/xdebug \
    && pie install osmanov/pecl-ev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql gd zip \
    && docker-php-ext-enable xdebug ev

RUN echo "xdebug.mode=debug,develop"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=127.0.0.1"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=VSCODE"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.discover_client_host=0"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log=/tmp/xdebug.log"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/html

COPY --from=bun_builder /app/node_modules ./node_modules
COPY composer.json composer.lock* ./
RUN composer install

COPY . .

RUN mkdir -p /etc/php/8.5/fpm/pool.d/ && cp -r ./config/www.developpment.conf /etc/php/8.5/fpm/pool.d/www.conf
RUN chown -R www-data:www-data /var/www/html


EXPOSE 9000

CMD ["php-fpm"]