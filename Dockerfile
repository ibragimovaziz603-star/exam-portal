# 1-BOSQICH: Node.js yordamida Vite assetlarini yig'ish
FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# 2-BOSQICH: Asosiy PHP image
FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    nginx

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

WORKDIR /var/www/html
COPY . .

# Node.js bosqichida yig'ilgan assetlarni ko'chirish
COPY --from=node_builder /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Keshni tozalash
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan route:clear || true

ENV DB_CONNECTION=pgsql

EXPOSE 80
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80