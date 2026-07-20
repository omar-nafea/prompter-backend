FROM php:8.3-fpm-alpine

# System deps + PHP extension build deps
RUN apk add --no-cache \
        nginx \
        libzip-dev \
        zip \
        unzip \
        oniguruma-dev \
        libpng-dev \
        icu-dev \
        mysql-client \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        bcmath \
        pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy project (incl. local path repos in Packages/ and Modules/)
COPY . .

# Install dependencies (path repos are resolved from Packages/)
RUN composer install --no-interaction --optimize-autoloader \
    && composer clear-cache

# Permissions for Laravel writable dirs
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
