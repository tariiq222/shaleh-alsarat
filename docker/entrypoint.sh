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
# 1. Wait for PostgreSQL (max 60s)
# ----------------------------------------------------------
if [[ "${DB_CONNECTION:-pgsql}" != "sqlite" ]]; then
    # Pull host + port out of DATABASE_URL (postgresql://user:pass@host:port/db?...)
    if [[ -n "${DATABASE_URL:-}" ]]; then
        DB_WAIT_HOST=$(echo "$DATABASE_URL" | sed -E 's|^postgres(ql)?://[^@]+@([^:/]+).*|\2|')
        DB_WAIT_PORT=$(echo "$DATABASE_URL" | sed -E 's|^postgres(ql)?://[^@]+@[^:/]+:([0-9]+).*|\2|')
        DB_WAIT_HOST="${DB_WAIT_HOST:-db}"
        DB_WAIT_PORT="${DB_WAIT_PORT:-5432}"
    else
        DB_WAIT_HOST="${DB_HOST:-db}"
        DB_WAIT_PORT="${DB_PORT:-5432}"
    fi

    echo "→ Waiting for PostgreSQL at ${DB_WAIT_HOST}:${DB_WAIT_PORT}..."
    ATTEMPTS=0
    MAX_ATTEMPTS=30
    until php -r "
        \$host = '${DB_WAIT_HOST}';
        \$port = (int) '${DB_WAIT_PORT}';
        \$errno = 0; \$errstr = '';
        \$sock = @stream_socket_client('tcp://'.\$host.':'.\$port, \$errno, \$errstr, 3);
        if (\$sock) { fclose(\$sock); exit(0); }
        exit(1);
    " 2>/dev/null; do
        ATTEMPTS=$((ATTEMPTS + 1))
        if [[ $ATTEMPTS -ge $MAX_ATTEMPTS ]]; then
            echo "✗ PostgreSQL not reachable after ${MAX_ATTEMPTS} attempts. Exiting."
            exit 1
        fi
        echo "  waiting ($ATTEMPTS/$MAX_ATTEMPTS)..."
        sleep 2
    done
    echo "✓ PostgreSQL port reachable."
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
