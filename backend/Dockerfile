# syntax=docker/dockerfile:1
# -----------------------------------------------------------------------------
# Laravel 11 / PHP 8.3-FPM image
# -----------------------------------------------------------------------------
FROM php:8.3-fpm-alpine

# System dependencies + PHP extensions required by Laravel + MySQL
RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        mysql-client \
    && docker-php-ext-install \
        pdo_mysql \
        bcmath \
        intl \
        zip \
        opcache

# Composer (copied from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Custom PHP config
COPY docker/php/local.ini /usr/local/etc/php/conf.d/zz-local.ini

WORKDIR /var/www

# Copy application source (volume mount overrides this in dev, but keeps the
# image self-contained for a standalone build)
COPY . /var/www

# Install PHP dependencies (no dev deps disabled — we want PHPUnit/Pest available
# so the reviewer can run the test suite inside the container)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && chown -R www-data:www-data /var/www

# Entrypoint waits for the DB, installs deps, generates key, migrates, then runs php-fpm
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
