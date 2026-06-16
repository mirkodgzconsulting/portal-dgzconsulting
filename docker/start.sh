#!/bin/sh
set -e

echo "==> Preparando directorios..."
mkdir -p database \
         storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/app/public \
         bootstrap/cache

chown -R www-data:www-data database storage bootstrap/cache

echo "==> Creando base de datos SQLite si no existe..."
[ -f database/database.sqlite ] || touch database/database.sqlite
chown www-data:www-data database/database.sqlite

echo "==> Ejecutando migraciones..."
php artisan migrate --force

echo "==> Optimizando Laravel..."
php artisan optimize

echo "==> Iniciando nginx + php-fpm..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
