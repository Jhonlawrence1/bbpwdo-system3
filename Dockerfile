FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql mysqli

COPY . /var/www/html/

RUN echo "Listen 8080" >> /etc/apache2/ports.conf

EXPOSE 8080

CMD ["apache2-foreground"]