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
