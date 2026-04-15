FROM php:8.2-apache

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    libzip-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_mysql mysqli

COPY web/bbpwdo-system/public/ /var/www/html/

RUN chmod -R 755 /var/www/html/

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-php-entrypoint"]
CMD ["apache2-foreground"]