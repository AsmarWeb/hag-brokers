FROM composer:2 AS composer_bin

FROM php:8.4-fpm-bookworm

# -------------------------------------------------------
# System dependencies + Node.js
# -------------------------------------------------------

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        ca-certificates \
        gnupg \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libxml2-dev \
    \
    # Node.js 22 LTS
    && mkdir -p /etc/apt/keyrings \
    && curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key \
        | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_22.x nodistro main" \
        > /etc/apt/sources.list.d/nodesource.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    \
    # PHP extensions
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        bcmath \
        intl \
        opcache \
        gd \
        zip \
    \
    # Redis
    && pecl install redis \
    && docker-php-ext-enable redis \
    \
    # Cleanup
    && rm -rf /var/lib/apt/lists/*

# -------------------------------------------------------
# Composer
# -------------------------------------------------------

COPY --from=composer_bin /usr/bin/composer /usr/bin/composer

# -------------------------------------------------------
# Laravel Bootstrap
# -------------------------------------------------------

COPY docker/php/bootstrap.sh /usr/local/bin/bootstrap.sh

# -------------------------------------------------------
# PHP configuration
# -------------------------------------------------------

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-trans-marine.ini

# -------------------------------------------------------
# Trans Marine application source
# -------------------------------------------------------

COPY src /opt/transmarine

# -------------------------------------------------------
# Permissions
# -------------------------------------------------------

RUN chmod +x /usr/local/bin/bootstrap.sh \
    && mkdir -p /var/www/html \
    && chown -R www-data:www-data /var/www/html /opt/transmarine

WORKDIR /var/www/html

# -------------------------------------------------------
# PHP-FPM
# -------------------------------------------------------

CMD ["php-fpm"]