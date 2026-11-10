#!/bin/sh
php artisan config:cache
php artisan view:cache
php artisan migrate --force
php-fpm -D
exec nginx -g 'daemon off;'
