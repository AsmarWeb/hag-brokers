FROM composer:2 AS composer_bin

FROM php:8.4-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libicu-dev libonig-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath intl opcache gd zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer_bin /usr/bin/composer /usr/bin/composer
COPY docker/php/bootstrap.sh /usr/local/bin/bootstrap.sh
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-trans-marine.ini
COPY src /opt/transmarine

RUN chmod +x /usr/local/bin/bootstrap.sh \
    && mkdir -p /var/www/html \
    && chown -R www-data:www-data /var/www/html /opt/transmarine

WORKDIR /var/www/html

CMD ["php-fpm"]
