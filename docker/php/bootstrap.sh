#!/bin/sh

set -eu

cd /var/www/html

echo "=============================================="
echo " HAG Brokers Laravel Bootstrap"
echo "=============================================="

# -------------------------------------------------------
# Create Laravel application if it does not exist
# -------------------------------------------------------

if [ ! -f artisan ]; then

    echo "Creating Laravel application..."

    rm -rf /tmp/trans-marine-laravel

    composer create-project laravel/laravel:^12.0 \
        /tmp/trans-marine-laravel \
        --no-interaction \
        --prefer-dist

    cp -a /tmp/trans-marine-laravel/. /var/www/html/
fi


# -------------------------------------------------------
# Install Sanctum
# -------------------------------------------------------

if ! composer show laravel/sanctum >/dev/null 2>&1; then

    echo "Installing Laravel Sanctum..."

    composer require laravel/sanctum \
        --no-interaction \
        --prefer-dist
fi


# -------------------------------------------------------
# Laravel API installation
# -------------------------------------------------------

php artisan install:api \
    --no-interaction \
    >/dev/null 2>&1 || true


# -------------------------------------------------------
# Overlay HAG Brokers application code
# -------------------------------------------------------

echo "Updating application files..."

if [ -d /opt/transmarine/app ]; then
    cp -a /opt/transmarine/app/. /var/www/html/app/
fi

if [ -d /opt/transmarine/database ]; then
    cp -a /opt/transmarine/database/. /var/www/html/database/
fi

if [ -f /opt/transmarine/routes/api.php ]; then
    cp -f /opt/transmarine/routes/api.php \
        /var/www/html/routes/api.php
fi


# -------------------------------------------------------
# Environment
# -------------------------------------------------------

if [ ! -f .env ]; then
    echo "Creating .env..."
    cp .env.example .env
fi


# -------------------------------------------------------
# Generate application key
# -------------------------------------------------------

php artisan key:generate \
    --force \
    --no-interaction \
    >/dev/null 2>&1 || true


# -------------------------------------------------------
# Docker environment overrides
# -------------------------------------------------------

php -r '
$p = ".env";

$s = file_get_contents($p);

$r = [
    "APP_ENV=local",
    "DB_CONNECTION=sqlite",
    "DB_DATABASE=/var/www/html/database/database.sqlite",
    "REDIS_HOST=redis",
    "QUEUE_CONNECTION=redis",
    "CACHE_STORE=redis",
    "SESSION_DRIVER=database",
    "MAIL_MAILER=smtp",
    "MAIL_HOST=mailpit",
    "MAIL_PORT=1025"
];

foreach ($r as $line) {

    [$k, $v] = array_pad(
        explode("=", $line, 2),
        2,
        ""
    );

    $pattern = "/^" .
        preg_quote($k, "/") .
        "=.*$/m";

    $s = preg_replace(
        $pattern,
        $line,
        $s,
        -1,
        $count
    );

    if (!$count) {
        $s .= "\n" . $line;
    }
}

file_put_contents($p, $s);
'


# -------------------------------------------------------
# SQLite
# -------------------------------------------------------

echo "Preparing SQLite database..."

mkdir -p database

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi


# -------------------------------------------------------
# Storage / Cache / Database permissions
# -------------------------------------------------------

echo "Setting permissions..."

mkdir -p \
    storage \
    bootstrap/cache \
    database

chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    database \
    2>/dev/null || true


# -------------------------------------------------------
# Clear Laravel caches
# -------------------------------------------------------

echo "Clearing Laravel caches..."

php artisan optimize:clear \
    --no-interaction \
    >/dev/null 2>&1 || true


# -------------------------------------------------------
# Database Bootstrap
# -------------------------------------------------------

if [ "${1:-}" = "bootstrap" ]; then

    echo "=============================================="
    echo " Running database migrations"
    echo "=============================================="

    php artisan migrate \
        --force \
        --no-interaction

    echo "=============================================="
    echo " Database migration completed"
    echo "=============================================="

    echo "Bootstrap completed successfully."

    exit 0
fi


# -------------------------------------------------------
# Execute container command
# -------------------------------------------------------

exec "$@"