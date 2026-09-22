FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends libssl-dev pkg-config unzip \
    && docker-php-ext-install pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite \
    && (getent group 1000 || groupadd -g 1000 render-secrets) \
    && usermod -a -G 1000 www-data \
    && sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:10000>/' /etc/apache2/sites-available/000-default.conf \
    && rm -rf /var/lib/apt/lists/*

# En ligne, les erreurs restent dans les journaux du serveur, pas sur les pages publiques.
RUN printf 'display_errors=Off\nlog_errors=On\n' > /usr/local/etc/php/conf.d/production-errors.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
COPY . .

EXPOSE 10000
CMD ["apache2-foreground"]
