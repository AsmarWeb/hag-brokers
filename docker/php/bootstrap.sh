#!/bin/sh
set -eu

cd /var/www/html

if [ ! -f artisan ]; then
  echo "Creating Laravel application..."
  rm -rf /tmp/trans-marine-laravel
  composer create-project laravel/laravel:^12.0 /tmp/trans-marine-laravel --no-interaction --prefer-dist
  cp -a /tmp/trans-marine-laravel/. /var/www/html/
fi

# Install API/Sanctum support once.
if ! composer show laravel/sanctum >/dev/null 2>&1; then
  composer require laravel/sanctum --no-interaction
fi

# Laravel 12's API installer creates Sanctum's migration and API bootstrap.
php artisan install:api --no-interaction >/dev/null 2>&1 || true

# Overlay Trans Marine application code after Laravel bootstrap.
cp -a /opt/transmarine/app/. /var/www/html/app/
cp -a /opt/transmarine/database/. /var/www/html/database/
cp -f /opt/transmarine/routes/api.php /var/www/html/routes/api.php

# Keep the local Docker environment predictable.
if [ ! -f .env ]; then
  cp .env.example .env
fi
php artisan key:generate --force --no-interaction >/dev/null 2>&1 || true

# Docker database/Redis overrides.
php -r '$p=".env"; $s=file_get_contents($p); $r=["APP_ENV=local","DB_CONNECTION=mysql","DB_HOST=mysql","DB_PORT=3306","DB_DATABASE=trans_marine","DB_USERNAME=trans_marine","DB_PASSWORD=trans_marine_secret","REDIS_HOST=redis","QUEUE_CONNECTION=redis","CACHE_STORE=redis","SESSION_DRIVER=database","MAIL_MAILER=smtp","MAIL_HOST=mailpit","MAIL_PORT=1025"]; foreach($r as $line){[$k,$v]=array_pad(explode("=",$line,2),2,""); $s=preg_replace("/^".preg_quote($k,"/")."=.*/m",$line,$s,-1,$count); if(!$count)$s.="\n$line";} file_put_contents($p,$s);'

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ "${1:-}" = "bootstrap" ]; then
  php artisan migrate --force --no-interaction
  exit 0
fi

exec "$@"
