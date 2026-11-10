FROM node:20-alpine AS node_builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libpq-dev nginx \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd opcache

WORKDIR /var/www/html
COPY . .
COPY --from=node_builder /app/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader

# PHP-FPM va OPcache sozlamalari
RUN printf "[www]\npm = dynamic\npm.max_children = 20\npm.start_servers = 6\npm.min_spare_servers = 4\npm.max_spare_servers = 10\n" > /usr/local/etc/php-fpm.d/zz-pool.conf \
    && printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.validate_timestamps=0\n" > /usr/local/etc/php/conf.d/opcache.ini

# Nginx
RUN rm -f /etc/nginx/sites-enabled/default
COPY docker/nginx.conf /etc/nginx/conf.d/app.conf

RUN chown -R www-data:www-data storage bootstrap/cache && chmod -R 775 storage bootstrap/cache
RUN chmod +x docker/start.sh

EXPOSE 80
CMD ["sh", "docker/start.sh"]