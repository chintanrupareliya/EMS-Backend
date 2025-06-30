FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libzip-dev \
    libpq-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libmcrypt-dev \
    libicu-dev \
    libxslt-dev \
    libgd-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel app files
COPY . /var/www

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 storage bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set environment variable
ENV APP_ENV=production

# Expose port (if you're using artisan serve — for development only)
EXPOSE 8000

# Set entrypoint to run migrations and start Laravel (dev only)
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
