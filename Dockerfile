# -----------------------------------------------------------
# Stage 1: Install Composer Dependencies
# -----------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# -----------------------------------------------------------
# Stage 2: Build Frontend (Inertia + Vue 3 + Vite)
# -----------------------------------------------------------
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package.json package-lock.json* pnpm-lock.yaml* ./
RUN npm ci || npm install

COPY . .
# Copy vendor from composer stage so Ziggy and vendor assets are available during build
COPY --from=vendor /app/vendor ./vendor

# VITE_* variables are baked into the bundle at build time, not runtime.
# Pass via --build-arg VITE_APP_NAME=YourApp during docker compose build.
ARG VITE_APP_NAME=Lentera
ENV VITE_APP_NAME=${VITE_APP_NAME}

RUN npm run build

# -----------------------------------------------------------
# Stage 3: Production PHP-FPM Application Image
# -----------------------------------------------------------
FROM php:8.4-fpm-alpine AS app

# Install system packages
RUN apk add --no-cache \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    linux-headers \
    su-exec

# Install PHP extensions needed by Laravel
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Custom PHP settings
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

WORKDIR /var/www/html

# Copy application code
COPY . .

# Copy vendor & build artifacts
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Set storage and cache permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Setup entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]

# -----------------------------------------------------------
# Stage 4: Production Nginx Web Server
# -----------------------------------------------------------
FROM nginx:alpine AS web

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
