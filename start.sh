#!/bin/sh
set -e

# Ensure permissions
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Ensure an .env exists
if [ ! -f .env ]; then
	if [ -f .env.example ]; then
		cp .env.example .env
	else
		echo "WARNING: .env and .env.example not found"
	fi
fi

# Generate app key if missing
php artisan key:generate --force || true

# Optionally run migrations when RUN_MIGRATIONS=true
if [ "${RUN_MIGRATIONS}" = "true" ]; then
	php artisan migrate --force
fi

# Optimize framework caches
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start the PHP built-in server (Render provides $PORT)
PORT=${PORT:-10000}
echo "Starting PHP server on 0.0.0.0:${PORT}"
exec php -S 0.0.0.0:${PORT} -t public
