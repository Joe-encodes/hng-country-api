# -----------------------------
# Base PHP-FPM build
# -----------------------------
    FROM php:8.3-fpm AS base

    # Install dependencies
    RUN apt-get update && apt-get install -y \
        git unzip ca-certificates libpng-dev libonig-dev libxml2-dev libzip-dev \
        libjpeg-dev libfreetype6-dev libssl-dev \
        && rm -rf /var/lib/apt/lists/*
    
    RUN update-ca-certificates
    
    # Install PHP extensions
    RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
     && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd zip opcache
    
    # Copy Composer
    COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
    
    WORKDIR /var/www
    
    # -----------------------------
    # Composer layer (for caching vendor)
    # -----------------------------
    FROM base AS vendor
    COPY composer.json composer.lock /var/www/
    RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
    
    # -----------------------------
    # Final app layer
    # -----------------------------
    FROM base AS app
    
    COPY . /var/www
    COPY --from=vendor /var/www/vendor /var/www/vendor
    
    # Copy CA certificate if provided (Aiven)
    # Use "|| true" to prevent error if missing
    COPY ca.pem /usr/local/share/ca-certificates/aiven.crt
    RUN update-ca-certificates || true
    
    # Ensure storage and cache permissions
    RUN chown -R www-data:www-data /var/www \
     && mkdir -p /var/www/storage/framework/cache/data \
     && mkdir -p /var/www/storage/logs \
     && chown -R www-data:www-data /var/www/storage
    
    # Defer artisan optimize to runtime (avoids missing env or DB)
    CMD php artisan config:cache && php artisan route:cache && php-fpm
    
    EXPOSE 9000
    