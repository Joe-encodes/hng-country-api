FROM php:8.3-fpm

# Install dependencies for extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip opcache pdo pdo_mysql \
    && docker-php-ext-enable gd zip opcache

# Optional: Clear cache to reduce image size
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
COPY . .

CMD ["php-fpm"]
FROM php:8.3-fpm AS base
RUN apt-get update && apt-get install -y \
    git unzip ca-certificates libpng-dev libonig-dev libxml2-dev libzip-dev \
    libjpeg-dev libfreetype6-dev && rm -rf /var/lib/apt/lists/*
RUN update-ca-certificates
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo pdo_mysql mbstring bcmath gd zip opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . /var/www  # <-- copy everything BEFORE composer install
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Optional if you have CA certs (like from Aiven)
COPY ca.pem /usr/local/share/ca-certificates/aiven.crt
RUN update-ca-certificates || true

RUN chown -R www-data:www-data /var/www \
 && php artisan key:generate || true \
 && php artisan optimize || true

EXPOSE 9000
CMD ["php-fpm"]
