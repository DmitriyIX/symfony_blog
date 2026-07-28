FROM php:8.5-fpm

WORKDIR /var/www

RUN apt-get update && \
    apt-get install -y libpq-dev libzip-dev && \
    docker-php-ext-install pdo pdo_pgsql pgsql pdo_mysql zip
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

EXPOSE 9000