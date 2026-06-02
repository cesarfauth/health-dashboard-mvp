#!/usr/bin/env bash
set -e

cd /var/www

echo "[entrypoint] Ensuring PHP dependencies are installed..."
if [ ! -d vendor ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

echo "[entrypoint] Ensuring .env exists..."
if [ ! -f .env ]; then
    cp .env.example .env
fi

echo "[entrypoint] Ensuring APP_KEY is set..."
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

echo "[entrypoint] Waiting for MySQL at ${DB_HOST:-db}:${DB_PORT:-3306}..."
until mysqladmin ping -h "${DB_HOST:-db}" -P "${DB_PORT:-3306}" --silent; do
    sleep 2
done
echo "[entrypoint] MySQL is up."

echo "[entrypoint] Running migrations..."
php artisan migrate --force

echo "[entrypoint] Caching config & routes..."
php artisan config:clear
php artisan route:clear

# Fix storage permissions (volume-mounted)
chown -R www-data:www-data storage bootstrap/cache || true

echo "[entrypoint] Starting: $*"
exec "$@"
