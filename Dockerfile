# ==========================================
# 1-BOSQICH: Node.js yordamida Vite assetlarini yig'ish
# ==========================================
FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# ==========================================
# 2-BOSQICH: Asosiy PHP image
# ==========================================
FROM php:8.4-fpm

# Kerakli paketlarni o'rnatish
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

# Composer'ni o'rnatish
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# PHP kengaytmalarini o'rnatish
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

WORKDIR /var/www/html
COPY . .

# Node.js bosqichida yig'ilgan assetlarni ko'chirish
COPY --from=node_builder /app/public/build ./public/build

# Composer o'rnatish
RUN composer install --no-dev --optimize-autoloader

# Ruxsatlarni sozlash
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# MUHIM: Keshni yig'ish (Build vaqtida)
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# Bazani majburan PostgreSQL ga o'zgartirish (BU YERDA XATO BOR EDI)
ENV DB_CONNECTION=pgsql
ENV DATABASE_URL=postgresql://neondb_owner:npg_GfiJIdEl7UH8@ep-royal-surf-bd7xru9o-pooler.c-8.us-east-2.aws.neon.tech/neondb?sslmode=require&channel_binding=require

EXPOSE 80

# Serverni ishga tushirish
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80