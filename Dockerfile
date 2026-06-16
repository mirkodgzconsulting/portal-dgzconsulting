FROM php:8.4-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    sqlite-dev \
    icu-dev \
    libzip-dev \
    oniguruma-dev

# PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install pdo_sqlite opcache pcntl gd intl zip

# OPcache tuned for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=10000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps (layer cached while composer.json/lock don't change)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copy the full application
COPY . .

# Post-install scripts (generates Filament/package discovery)
RUN composer run-script post-autoload-dump --no-interaction 2>/dev/null || true

# Docker configs
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /start.sh
RUN chmod +x /start.sh

# Writable dirs (volumes will be mounted here — chown covers the image layer)
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
             storage/logs storage/app/public bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 80

CMD ["/start.sh"]
