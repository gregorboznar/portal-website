FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN echo "upload_max_filesize = 50M" > /usr/local/etc/php/conf.d/upload.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/upload.ini \
    && echo "max_file_uploads = 20" >> /usr/local/etc/php/conf.d/upload.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/upload.ini

COPY . .

RUN composer install --optimize-autoloader --no-interaction --no-dev

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"] 