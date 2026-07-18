#!/bin/sh
set -e

# Default PORT
: ${PORT:=10000}

# Ensure permissions
chown -R www-data:www-data /var/www || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache || true

# Ensure .env exists
if [ ! -f /var/www/.env ]; then
  if [ -f /var/www/.env.example ]; then
    cp /var/www/.env.example /var/www/.env
  fi
fi

cd /var/www

# Generate app key if missing
if [ -f artisan ]; then
  php artisan key:generate --force || true
fi

# Optionally run migrations
if [ "${RUN_MIGRATIONS}" = "true" ] && [ -f artisan ]; then
  php artisan migrate --force || true
fi

# Replace nginx listen port in config
NGINX_CONF=/etc/nginx/sites-enabled/default
if [ -f "$NGINX_CONF" ]; then
  sed -i "s/listen[[:space:]]\+[0-9]\+;/listen ${PORT};/" "$NGINX_CONF" || true
fi

echo "Entrypoint: starting supervisord (nginx -> php-fpm) on PORT=${PORT}"

exec /usr/bin/supervisord -n
