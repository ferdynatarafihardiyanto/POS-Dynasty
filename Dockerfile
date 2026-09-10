# ==============================================================================
# STAGE 1: Build Frontend Assets (React 19 + Vite + Tailwind CSS)
# ==============================================================================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ==============================================================================
# STAGE 2: PHP 8.3 FPM + Nginx + Laravel Production Runtime
# ==============================================================================
FROM php:8.3-fpm-alpine

# Install system dependencies & PHP build tools
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    mariadb-client \
    nginx \
    supervisor \
    netcat-openbsd

# Konfigurasi & install ekstensi PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache

# Salin Composer resmi versi 2
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Salin source code projek
COPY . .

# Salin hasil kompilasi frontend dari Stage 1
COPY --from=frontend /app/public/build ./public/build

# Install dependensi PHP tanpa dev packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Konfigurasi Nginx, Supervisor, dan Entrypoint
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Atur perizinan direktori storage & bootstrap cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
