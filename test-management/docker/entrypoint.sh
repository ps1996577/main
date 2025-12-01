#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="/var/www/html"
cd "${PROJECT_DIR}"

if [[ ! -f "composer.json" ]]; then
    echo "[entrypoint] composer.json not found inside container. Did you mount the project directory?" >&2
    exit 1
fi

export COMPOSER_ALLOW_SUPERUSER=1

if [[ ! -f "vendor/autoload.php" ]]; then
    echo "[entrypoint] Installing PHP dependencies via Composer…"
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [[ -f "package-lock.json" ]]; then
    if [[ ! -d "node_modules" || -z "$(ls -A node_modules 2>/dev/null)" ]]; then
        echo "[entrypoint] Installing Node dependencies…"
        npm ci
    fi

    if [[ ! -f "public/build/manifest.json" ]]; then
        echo "[entrypoint] Building front-end assets with Vite…"
        npm run build
    fi
fi

if [[ ! -f ".env" ]]; then
    echo "[entrypoint] Creating .env from .env.example…"
    cp .env.example .env
fi

APP_KEY_LINE=$(grep '^APP_KEY=' .env || true)
if [[ -z "${APP_KEY_LINE#APP_KEY=}" ]]; then
    echo "[entrypoint] Generating APP_KEY…"
    php artisan key:generate --force --no-interaction
fi

mkdir -p storage/framework/{cache,sessions,views} storage/app/public storage/logs database
if [[ ! -f "database/database.sqlite" ]]; then
    echo "[entrypoint] Creating SQLite database file…"
    touch database/database.sqlite
fi

php artisan storage:link >/dev/null 2>&1 || true

if [[ "${RUN_MIGRATIONS:-true}" == "true" ]]; then
    echo "[entrypoint] Running database migrations…"
    php artisan migrate --force --no-interaction
fi

if [[ "${SEED_DATABASE:-false}" == "true" ]]; then
    echo "[entrypoint] Seeding database…"
    php artisan db:seed --force --no-interaction
fi

APP_PORT=${APP_PORT:-8000}
echo "[entrypoint] Starting Laravel development server on port ${APP_PORT}…"
exec php artisan serve --host=0.0.0.0 --port="${APP_PORT}"
