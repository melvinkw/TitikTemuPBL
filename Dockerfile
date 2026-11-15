
# ===================================
# STAGE 1: BUILD TAILWIND CSS
# ===================================
FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY resources/ ./resources/
COPY Public/ ./Public/

RUN mkdir -p Public/assets/css && npm run build

# ===================================
# STAGE 2: PHP APACHE
# ===================================
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libcurl4-openssl-dev \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo_pgsql curl zip \
    && a2enmod setenvif headers \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependency PHP
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader

# Source aplikasi
COPY . .

# CSS hasil build
COPY --from=assets /app/Public/assets/css/app.css \
    /var/www/html/Public/assets/css/app.css

# Konfigurasi Apache
RUN sed -ri \
    's!/var/www/html!/var/www/html/Public!g' \
    /etc/apache2/sites-available/000-default.conf \
    && sed -ri 's/Listen 80/Listen 10000/' \
    /etc/apache2/ports.conf \
    && sed -ri \
    's/<VirtualHost \*:80>/<VirtualHost *:10000>/' \
    /etc/apache2/sites-available/000-default.conf

# Membaca environment variable ke $_ENV
RUN printf 'variables_order=EGPCS\n' \
    > /usr/local/etc/php/conf.d/zz-env.ini

# Mengenali HTTPS dari reverse proxy Render
RUN printf '\nSetEnvIf X-Forwarded-Proto "^https$" HTTPS=on\n' \
    >> /etc/apache2/apache2.conf

EXPOSE 10000

CMD ["apache2-foreground"]