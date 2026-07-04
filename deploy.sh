#!/usr/bin/env bash
# deploy.sh — manual deploy script for the Chalet MVP
#
# Usage:
#   ./deploy.sh                       # deploy main branch to current dir
#   ./deploy.sh release/2026-07-04    # deploy a specific branch
#
# Assumptions:
#   - Repo is cloned at $APP_DIR (default: cwd)
#   - .env is configured (DB, ADMIN_EMAIL, ADMIN_PASSWORD, APP_KEY, etc.)
#   - PHP-FPM runs under systemd as php8.2-fpm (override RELOAD_CMD otherwise)
#
# Make executable once on the server:
#   chmod +x deploy.sh

set -euo pipefail

BRANCH="${1:-main}"
APP_DIR="${APP_DIR:-$(pwd)}"
RELOAD_CMD="${RELOAD_CMD:-}"

echo "==> Deploying branch '${BRANCH}' to ${APP_DIR}"

cd "$APP_DIR"

echo "==> Pulling latest code"
git fetch origin
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

echo "==> Installing composer dependencies (production)"
composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-dist

echo "==> Installing npm dependencies and building assets"
npm ci --no-audit --no-fund
npm run build

echo "==> Running database migrations"
php artisan migrate --force

echo "==> Linking storage directory (so /storage/* URLs resolve)"
php artisan storage:link --force

echo "==> Clearing and caching config/routes/views"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Reloading PHP-FPM"
if [[ -n "$RELOAD_CMD" ]]; then
    eval "$RELOAD_CMD" || echo "    reload command failed; please reload manually"
elif command -v systemctl >/dev/null 2>&1; then
    if systemctl is-active --quiet php8.2-fpm; then
        sudo systemctl reload php8.2-fpm || echo "    systemctl reload failed; please reload manually"
    else
        echo "    php8.2-fpm not active; skipping reload"
    fi
else
    echo "    systemctl not available; reload PHP-FPM manually"
fi

echo "==> Deploy complete!"
echo "    Branch: ${BRANCH}"
echo "    Time:   $(date -u +%Y-%m-%dT%H:%M:%SZ)"