FROM php:8.3-cli

COPY . /usr/src/api
WORKDIR /usr/src/api
CMD ["php","./rest.php"]