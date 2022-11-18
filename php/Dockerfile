FROM php:7.4-apache
COPY ./php.ini /usr/local/etc/php/
COPY ./openssl.cnf /etc/ssl/
RUN apt update \
  && apt install -y libfreetype6-dev libjpeg62-turbo-dev libonig-dev libpng-dev \
  && docker-php-ext-install pdo_mysql mysqli mbstring gd iconv \
  && a2enmod rewrite
