FROM php:8.3-fpm AS base
RUN apt-get update && apt-get install -y \
    git unzip ca-certificates libpng-dev libonig-dev libxml2-dev libzip-dev \
    libjpeg-dev libfreetype6-dev && rm -rf /var/lib/apt/lists/*
RUN update-ca-certificates
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd zip opcache
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www

FROM base AS vendor
COPY composer.json composer.lock /var/www/
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

FROM base AS app
COPY . /var/www
COPY --from=vendor /var/www/vendor /var/www/vendor
# Trust custom CA if provided in repo (Aiven, etc.)
COPY ca.pem /usr/local/share/ca-certificates/aiven.crt
RUN update-ca-certificates || true
RUN chown -R www-data:www-data /var/www \
 && php -r "file_exists('public/storage') || @symlink('/var/www/storage/app/public', '/var/www/public/storage');" \
 && php -d detect_unicode=0 artisan optimize || true
EXPOSE 9000
CMD ["php-fpm"]




