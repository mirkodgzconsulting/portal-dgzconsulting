#!/bin/sh
set -e

echo "==> Generando .env desde variables de entorno..."
if [ ! -f .env ]; then
  printenv | grep -E "^APP_|^DB_|^SESSION_|^CACHE_|^LOG_|^AWS_|^R2_|^FILESYSTEM_|^QUEUE_|^BROADCAST_|^VITE_|^CURATOR_" | while IFS= read -r line; do
    key="${line%%=*}"
    val="${line#*=}"
    printf '%s="%s"\n' "$key" "$val"
  done > .env
fi

echo "==> Preparando directorios..."
mkdir -p database \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         storage/app/public \
         bootstrap/cache

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data database storage bootstrap/cache

echo "==> Ejecutando migraciones..."
su -s /bin/sh www-data -c "php artisan migrate --force"

echo "==> Limpiando cache..."
su -s /bin/sh www-data -c "php artisan config:clear"
su -s /bin/sh www-data -c "php artisan cache:clear"
su -s /bin/sh www-data -c "php artisan route:cache"

echo "==> Iniciando nginx + php-fpm..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
