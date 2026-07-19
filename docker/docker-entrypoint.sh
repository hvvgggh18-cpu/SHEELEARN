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

# Run Composer autoload and package discovery at container startup (after .env and APP_KEY exist)
if [ -f composer.json ]; then
  composer dump-autoload --optimize --no-interaction || true
  if [ -f artisan ]; then
    php artisan package:discover --ansi || true
  fi
fi

# Clear and rebuild Laravel caches so runtime env vars are loaded correctly
if [ -f artisan ]; then
  php artisan config:clear || true
  php artisan route:clear || true
  php artisan view:clear || true
  php artisan config:cache || true
  php artisan route:cache || true
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
