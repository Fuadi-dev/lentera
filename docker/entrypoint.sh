#!/bin/sh
set -e

# Ensure storage directory structure exists (Docker volumes may be empty on first run)
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Fix ownership at runtime — build-time chown is lost when Docker volume is mounted
chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

chmod -R 775 \
    /var/www/html/storage \
    /var/www/html/bootstrap/cache

# Ensure storage symlink (run as root since public/ is owned by root in image)
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link --force || true
fi

# php-fpm master runs as root, worker processes run as www-data (via www.conf)
exec "$@"

