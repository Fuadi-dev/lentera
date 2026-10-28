#!/bin/sh
set -e

# Ensure storage directory structure exists
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

# Ensure storage symlink
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

exec "$@"
