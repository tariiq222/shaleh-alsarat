#!/usr/bin/env bash
# ================================================================
# Entrypoint for shaleh-alsarat Laravel container
# ================================================================
# What it does:
#   1. Wait for MySQL to be reachable
#   2. Ensure storage/ and bootstrap/cache/ are writable
#   3. Run migrations + seeders (only if not yet done)
#   4. Link storage/app/public -> public/storage
#   5. Cache config/routes/views for performance
#   6. Hand off to the main process (apache2-foreground)
# ================================================================

set -e

cd /var/www/html

echo "==========================================="
echo "  shaleh-alsarat container starting..."
echo "==========================================="

# ----------------------------------------------------------
# 1. Wait for MySQL (max 60s)
# ----------------------------------------------------------
if [[ -n "${DB_HOST:-}" ]] && [[ "${DB_CONNECTION:-mysql}" != "sqlite" ]]; then
    echo "→ Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
    ATTEMPTS=0
    MAX_ATTEMPTS=30
    until mysqladmin ping \
        --host="${DB_HOST}" \
        --port="${DB_PORT:-3306}" \
        --user="${DB_USERNAME}" \
        --password="${DB_PASSWORD}" \
        --silent 2>/dev/null; do
        ATTEMPTS=$((ATTEMPTS + 1))
        if [[ $ATTEMPTS -ge $MAX_ATTEMPTS ]]; then
            echo "✗ MySQL not reachable after ${MAX_ATTEMPTS} attempts. Exiting."
            exit 1
        fi
        echo "  waiting ($ATTEMPTS/$MAX_ATTEMPTS)..."
        sleep 2
    done
    echo "✓ MySQL is up."
fi

# ----------------------------------------------------------
# 2. Storage & cache permissions
# ----------------------------------------------------------
mkdir -p storage/logs \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/app/public

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

# ----------------------------------------------------------
# 3. APP_KEY (idempotent — only generates if missing)
# ----------------------------------------------------------
if [[ -z "${APP_KEY:-}" || "${APP_KEY:-}" == "base64:" ]]; then
    echo "→ Generating APP_KEY..."
    php artisan key:generate --force
fi

# ----------------------------------------------------------
# 4. Public storage symlink
# ----------------------------------------------------------
if [[ ! -e public/storage ]]; then
    echo "→ Linking storage directory..."
    php artisan storage:link || true
fi

# ----------------------------------------------------------
# 5. Migrations (only runs pending ones; safe to re-run)
# ----------------------------------------------------------
echo "→ Running migrations..."
php artisan migrate --force --no-interaction

# ----------------------------------------------------------
# 6. Seeders (only if no admin exists — preserves edits)
# ----------------------------------------------------------
if ! php artisan tinker --execute='echo App\Models\User::count();' 2>/dev/null | grep -q '^[1-9]'; then
    echo "→ Seeding admin user + ChaletSettings..."
    php artisan db:seed --force --no-interaction || true
else
    echo "✓ Admin user already exists; skipping seeders."
fi

# ----------------------------------------------------------
# 7. Optimize (config/route/view caches)
# ----------------------------------------------------------
echo "→ Optimizing for production..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ----------------------------------------------------------
# 8. Warm storage symlinks so cache files persist
# ----------------------------------------------------------
php artisan storage:link >/dev/null 2>&1 || true

echo "==========================================="
echo "  Ready. Starting Apache..."
echo "==========================================="

# Hand off to the main CMD (apache2-foreground)
exec "$@"
