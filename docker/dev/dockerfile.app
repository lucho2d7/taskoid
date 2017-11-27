FROM php:7-fpm

RUN apt-get update && apt-get install -y libmcrypt-dev pkg-config libssl-dev \
&& docker-php-ext-install mcrypt

# install mongodb ext
RUN pecl install mongodb \
&& docker-php-ext-enable mongodb

WORKDIR /var/www