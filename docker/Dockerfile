# syntax=docker/dockerfile:1
# Multi-stage: assets (Vite) + dependências PHP, imagem final PHP-FPM

# -----------------------------------------------------------------------------
# Stage 1 — frontend (Vite)
# -----------------------------------------------------------------------------
FROM node:22-bookworm-slim AS frontend
WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .
RUN npm run build

# -----------------------------------------------------------------------------
# Stage 2 — Composer (vendor)
# -----------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app

COPY . .
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# -----------------------------------------------------------------------------
# Stage 3 — PHP-FPM
# -----------------------------------------------------------------------------
FROM php:8.3-fpm-bookworm

LABEL org.opencontainers.image.title="Bolso Planejado"
LABEL org.opencontainers.image.description="Laravel + Vue (PHP-FPM)"

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    nginx \
    gettext-base \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        pdo_mysql \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        zip \
        opcache \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Código da aplicação (sem vendor/node_modules graças ao .dockerignore)
COPY . .

COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Nginx: usa template com porta dinâmica ($PORT) para PaaS (ex.: Render)
RUN mkdir -p /etc/nginx/conf.d
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf.tpl
# Ajusta template para rodar single-container: porta dinâmica e upstream local do PHP-FPM
RUN sed -i 's/listen 80;/listen ${PORT};/g' /etc/nginx/conf.d/default.conf.tpl \
    && sed -i 's/listen \\[::\\]:80;/listen \\[::\\]:${PORT};/g' /etc/nginx/conf.d/default.conf.tpl \
    && sed -i 's/fastcgi_pass app:9000;/fastcgi_pass 127.0.0.1:9000;/g' /etc/nginx/conf.d/default.conf.tpl
RUN rm -f /etc/nginx/conf.d/default.conf || true

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
