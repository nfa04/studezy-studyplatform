FROM php:8.3-apache

RUN apt update && apt -y upgrade && apt install -y curl git zip unzip libcurl4-openssl-dev sendmail \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html/

COPY src .

RUN composer update && composer install && docker-php-ext-install curl pdo pdo_mysql && a2enmod rewrite && a2enmod ssl

EXPOSE 80
EXPOSE 443

ENV APACHE_DOCUMENT_ROOT=/var/www/html/

CMD ["apache2-foreground"]
