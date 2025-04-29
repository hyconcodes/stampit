# Stage 1: Build Laravel with Composer
FROM composer:2 as build

WORKDIR /app

COPY . /app

RUN composer install --no-dev --optimize-autoloader

# Stage 2: Production container with PHP-FPM and Nginx
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Copy Laravel app from build stage
COPY --from=build /app /var/www

# Set working directory
WORKDIR /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage /var/www/bootstrap/cache

# Nginx config
COPY ./docker/nginx/default.conf /etc/nginx/sites-available/default

# Supervisor config to run both Nginx and PHP-FPM
COPY ./docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 80

CMD ["/usr/bin/supervisord"]
