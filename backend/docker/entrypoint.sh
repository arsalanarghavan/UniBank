#!/bin/sh
set -e
cd /var/www/html

if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

php artisan config:clear || true
php artisan migrate --force --no-interaction || true
php artisan db:seed --force --no-interaction || true

exec "$@"
