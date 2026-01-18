FROM php:latest

RUN apt-get update --yes && apt-get upgrade --yes && apt-get install git --yes && docker-php-ext-install pdo pdo_mysql

COPY . /usr/src/api
WORKDIR /usr/src/api
CMD ["php", "-S", "0.0.0.0:80", "./rest.php"]