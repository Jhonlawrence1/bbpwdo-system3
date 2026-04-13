FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli pdo_pgsql gd zip

COPY public/ /var/www/html/

RUN chmod -R 755 /var/www/html/ \
    && chmod -R 777 /var/www/html/backend/

EXPOSE 80

CMD ["apache2-foreground"]
