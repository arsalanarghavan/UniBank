FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y \
    git unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev \
    nginx supervisor default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath intl pcntl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Prefer vendored deps when present (avoids GitHub/codeload timeouts).
# Otherwise install from Packagist with retries.
RUN set -eux; \
    if [ -f vendor/autoload.php ]; then \
      echo "Using pre-vendored dependencies"; \
    else \
      for i in 1 2 3 4 5; do \
        composer install --no-dev --no-scripts --no-interaction --prefer-dist && break; \
        echo "composer install failed (attempt $i), retrying..."; \
        sleep $((i * 5)); \
      done; \
    fi; \
    test -f vendor/autoload.php; \
    composer dump-autoload --optimize --no-scripts; \
    php artisan package:discover --ansi || true; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
