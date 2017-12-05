FROM php:7-fpm

RUN apt-get update && apt-get install -y pkg-config apt-utils
RUN apt-get install -y libssl-dev zip unzip git

# install mongodb ext
RUN pecl install mongodb \
&& docker-php-ext-enable mongodb

WORKDIR /tmp

RUN apt-get install -y wget
RUN wget https://phar.phpunit.de/phpunit.phar
RUN chmod +x phpunit.phar
RUN mv phpunit.phar /usr/bin/phpunit
RUN wget https://getcomposer.org/download/1.5.5/composer.phar
RUN chmod +x composer.phar
RUN mv composer.phar /usr/local/bin/composer

ADD ./src /var/www

WORKDIR /var/www
RUN cp .env.example .env
RUN composer install
RUN chown www-data:www-data -R /var/www
RUN chmod -R 775 storage/ bootstrap/cache
#RUN ls storage/ storage/logs storage/framework storage/framework/cache