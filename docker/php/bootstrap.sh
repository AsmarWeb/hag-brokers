#!/bin/sh

set -eu

cd /var/www/html

echo "=============================================="
echo " HAG Brokers Laravel Bootstrap"
echo "=============================================="

# -------------------------------------------------------
# Validate existing Laravel application
# -------------------------------------------------------

if [ ! -f artisan ]; then
    echo "ERROR: Laravel application is missing (artisan not found)." >&2
    exit 1
fi

if [ ! -f vendor/autoload.php ]; then
    echo "ERROR: Composer dependencies are missing." >&2
    echo "Install dependencies explicitly before starting Docker." >&2
    exit 1
fi

if ! php -r '
require "vendor/autoload.php";
if (!class_exists("Illuminate\\Foundation\\Application")) {
    fwrite(STDERR, "Laravel framework class not found.\n");
    exit(1);
}
'; then
    echo "ERROR: Laravel dependencies are invalid or incomplete." >&2
    exit 1
fi

echo "Existing Laravel application verified."


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

if ! grep -Eq '^APP_KEY=.+$' .env; then
    echo "Generating Laravel APP_KEY for first-time setup..."
    php artisan key:generate --no-interaction
else
    echo "Existing Laravel APP_KEY preserved."
fi


# -------------------------------------------------------
# Docker environment defaults
# -------------------------------------------------------

php -r '
$p = ".env";
$s = file_get_contents($p);

$defaults = [
    "APP_ENV" => "local",
    "DB_CONNECTION" => "sqlite",
    "DB_DATABASE" => "/var/www/html/database/database.sqlite",
    "REDIS_HOST" => "redis",
    "QUEUE_CONNECTION" => "redis",
    "CACHE_STORE" => "redis",
    "SESSION_DRIVER" => "database",
    "MAIL_MAILER" => "smtp",
    "MAIL_HOST" => "mailpit",
    "MAIL_PORT" => "1025"
];

foreach ($defaults as $key => $value) {
    if (!preg_match("/^" . preg_quote($key, "/") . "=/m", $s)) {
        $s .= "\n" . $key . "=" . $value;
    }
}

if ($s !== file_get_contents($p)) {
    file_put_contents($p, $s);
}
'

# -------------------------------------------------------
# SQLite database initialization
# -------------------------------------------------------

DB_DRIVER=$(php -r '
foreach (file(".env") as $line) {
    if (preg_match("/^DB_CONNECTION=(.*)$/", trim($line), $m)) {
        echo trim($m[1], "\"\047");
        break;
    }
}
')

if [ "$DB_DRIVER" = "sqlite" ]; then
    echo "Preparing SQLite database..."
    mkdir -p database

    if [ ! -f database/database.sqlite ]; then
        touch database/database.sqlite
    fi
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

if ! php artisan optimize:clear --no-interaction; then
    echo "ERROR: Laravel cache clearing failed." >&2
    exit 1
fi


# -------------------------------------------------------
# Bootstrap readiness check
# -------------------------------------------------------

if [ "${1:-}" = "bootstrap" ]; then
    echo "Bootstrap completed successfully."
    echo "Database migrations must be run separately."
    exit 0
fi


# -------------------------------------------------------
# Execute container command
# -------------------------------------------------------

exec "$@"