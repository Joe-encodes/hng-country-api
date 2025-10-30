# ------------------------------
# Stage 1 - Base
# ------------------------------
    FROM php:8.3-fpm AS base

    RUN apt-get update && apt-get install -y \
        git unzip ca-certificates libpng-dev libonig-dev libxml2-dev libzip-dev \
        libjpeg-dev libfreetype6-dev && rm -rf /var/lib/apt/lists/*
    
    RUN update-ca-certificates
    
    RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
     && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd zip opcache
    
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    WORKDIR /var/www
    
    # ------------------------------
    # Stage 2 - Vendor (dependencies only)
    # ------------------------------
    FROM base AS vendor
    COPY composer.json composer.lock /var/www/
    RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
        --no-dev --no-interaction --prefer-dist --optimize-autoloader
    
    # ------------------------------
    # Stage 3 - App
    # ------------------------------
    FROM base AS app
    
    # Copy app files
    COPY . /var/www
    COPY --from=vendor /var/www/vendor /var/www/vendor
    
    # Copy Aiven CA cert if exists
    # (You’ll update the file path below)
    COPY ca.pem /usr/local/share/ca-certificates/aiven.crt
    RUN update-ca-certificates || true
    
    # Fix permissions and optimize Laravel
    RUN chown -R www-data:www-data /var/www \
     && php -r "file_exists('public/storage') || @symlink('/var/www/storage/app/public', '/var/www/public/storage');" \
     && php artisan optimize || true
    
    EXPOSE 9000
    CMD ["php-fpm"]
    