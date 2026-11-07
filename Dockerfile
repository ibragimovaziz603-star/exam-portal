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

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Keshni tozalash
RUN php artisan config:clear || true
RUN php artisan cache:clear || true
RUN php artisan route:clear || true

# BAZANI MAJBURAN POSTGRESQL QILISH
ENV DB_CONNECTION=pgsql

EXPOSE 80
CMD php artisan serve --host=0.0.0.0 --port=80