FROM php:8.2-apache

RUN docker-php-ext-install pdo_mysql mysqli

COPY . /var/www/html/

EXPOSE 8000

CMD ["sh", "-c", "sed -i 's/Listen 80/Listen 8000/g' /etc/apache2/ports.conf && apache2-foreground"]