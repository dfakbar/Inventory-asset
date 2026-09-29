FROM php:8.2-fpm

# System deps + ekstensi PHP yang dibutuhkan Laravel + dompdf (GD opsional utk PDF ber-gambar)
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip zip libzip-dev libonig-dev libxml2-dev \
        libpng-dev libfreetype6-dev libjpeg62-turbo-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql pdo_sqlite mbstring xml zip bcmath gd \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Layer dependency (cache hanya invalid jika composer.json/lock berubah)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

COPY . .
# --no-dev: image produksi; scripts jalan setelah source lengkap (package:discover)
RUN composer install --no-dev --no-interaction --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data

EXPOSE 9000
CMD ["php-fpm"]
