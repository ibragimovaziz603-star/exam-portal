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

# Composer o'rnatish
RUN composer install --no-dev --optimize-autoloader

# MUHIM: storage va bootstrap/cache papkalariga yozish huquqini berish
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# MUHIM: .env fayli bo'lmasa ham ishlashi uchun
RUN cp .env.example .env || true
RUN php artisan key:generate --force || true
RUN php artisan config:cache || true

EXPOSE 80
CMD php artisan serve --host=0.0.0.0 --port=80