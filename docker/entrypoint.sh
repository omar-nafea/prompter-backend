#!/bin/sh
set -e

# Ensure writable dirs (host bind mount may override build-time ownership)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Ensure .env exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate APP_KEY if missing
if ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Wait for MySQL
echo "Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306} ..."
max=60
i=0
until php -r "new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306}', '${DB_USERNAME:-root}', '${DB_PASSWORD:-secret}');" 2>/dev/null; do
    i=$((i + 1))
    if [ "$i" -ge "$max" ]; then
        echo "MySQL not reachable, skipping migration."
        break
    fi
    echo "  retry ($i/$max)..."
    sleep 2
done

# Run migrations + seeders
php artisan migrate --force || echo "Migration skipped/failed."
php artisan storage:link 2>/dev/null || true

exec "$@"
