FROM ghcr.io/devgine/composer-php:v2-php8.4-alpine

# RUN apt-get update && apt-get install -y \
#     git unzip zip curl libpng-dev libonig-dev libxml2-dev \
#     && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd

WORKDIR /var/www/html

COPY . /var/www/html

RUN composer install --no-dev --optimize-autoloader

RUN php artisan optimize

RUN php artisan config:cache

RUN php artisan event:cache

RUN php artisan route:cache

RUN php artisan view:cache