#!/bin/sh
set -e
cd /var/www/html

if [ ! -d vendor ]; then
  composer install --no-interaction --prefer-dist
fi

# Compose/aaPanel often mounts env via process environment without a .env file.
if [ -z "${APP_KEY:-}" ]; then
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
  echo "Generated ephemeral APP_KEY for this container boot"
fi

if [ ! -f .env ]; then
  printf 'APP_KEY=%s\n' "$APP_KEY" > .env
fi

php artisan config:clear || true
php artisan migrate --force --no-interaction || true
php artisan db:seed --force --no-interaction || true

exec "$@"
