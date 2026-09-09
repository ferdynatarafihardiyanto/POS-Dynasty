#!/bin/sh
set -e

# Buat .env jika belum ada
if [ ! -f /var/www/.env ]; then
    echo "[Entrypoint] Creating .env from .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

# Pastikan APP_KEY terisi
if grep -q "APP_KEY=$" /var/www/.env || grep -q "APP_KEY=" /var/www/.env && [ -z "$(grep 'APP_KEY=base64' /var/www/.env)" ]; then
    echo "[Entrypoint] Generating application key..."
    php /var/www/artisan key:generate --force
fi

# Link storage
php /var/www/artisan storage:link || true

# Tunggu database siap jika koneksi MySQL/MariaDB diset
if [ "$DB_CONNECTION" = "mysql" ] && [ -n "$DB_HOST" ]; then
    echo "[Entrypoint] Waiting for database connection ($DB_HOST:${DB_PORT:-3306})..."
    until nc -z -v -w30 "$DB_HOST" "${DB_PORT:-3306}" >/dev/null 2>&1; do
        echo "[Entrypoint] Waiting for database at $DB_HOST:${DB_PORT:-3306}..."
        sleep 2
    done
    echo "[Entrypoint] Database is ready! Running migrations & seeders..."
    php /var/www/artisan migrate --force || true
fi

# Bersihkan cache agar rute dan tampilan selalu update
php /var/www/artisan config:clear || true
php /var/www/artisan route:clear || true
php /var/www/artisan view:clear || true

# Atur kepemilikan permission storage & cache
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "[Entrypoint] Starting Nginx & PHP-FPM via Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
