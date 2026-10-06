#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f artisan ]; then
  echo "Creating Laravel application..."

  rm -rf /tmp/trans-marine-laravel

  composer create-project laravel/laravel:^12.0 \
    /tmp/trans-marine-laravel \
    --no-interaction \
    --prefer-dist

  cp -a /tmp/trans-marine-laravel/. /var/www/html/
fi

# Install API/Sanctum support once.
if ! composer show laravel/sanctum >/dev/null 2>&1; then
  composer require laravel/sanctum --no-interaction
fi

# Laravel 12 API installer.
php artisan install:api --no-interaction >/dev/null 2>&1 || true

# Overlay Trans Marine application code.
cp -a /opt/transmarine/app/. /var/www/html/app/
cp -a /opt/transmarine/database/. /var/www/html/database/
cp -f /opt/transmarine/routes/api.php /var/www/html/routes/api.php

# Create .env if it does not exist.
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Generate Laravel application key.
php artisan key:generate --force --no-interaction >/dev/null 2>&1 || true

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
    [$k, $v] = array_pad(explode("=", $line, 2), 2, "");

    $pattern = "/^" . preg_quote($k, "/") . "=.*$/m";

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

mkdir -p database
touch database/database.sqlite

# Permissions
chown -R www-data:www-data \
  storage \
  bootstrap/cache \
  database \
  2>/dev/null || true

# -------------------------------------------------------
# Bootstrap
# -------------------------------------------------------

if [ "${1:-}" = "bootstrap" ]; then

  echo "Running database migrations..."

  php artisan migrate --force --no-interaction

  echo "Bootstrap completed successfully."

  exit 0
fi

exec "$@"