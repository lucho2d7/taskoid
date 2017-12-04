FROM php:7-fpm

RUN apt-get update && apt-get install -y libmcrypt-dev pkg-config libssl-dev \
&& docker-php-ext-install mcrypt

# install mongodb ext
RUN pecl install mongodb \
&& docker-php-ext-enable mongodb

WORKDIR /tmp

RUN apt-get install -y wget
RUN wget https://phar.phpunit.de/phpunit.phar
RUN chmod +x phpunit.phar
RUN mv phpunit.phar /usr/bin/phpunit

WORKDIR /var/www