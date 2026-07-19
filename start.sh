#!/bin/sh

set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan key:generate --force || true

php artisan config:clear
php artisan cache:clear

php artisan config:cache

php artisan migrate --force || true

php artisan storage:link || true

php artisan serve \
    --host=0.0.0.0 \
    --port=${PORT}  