FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache configuration
RUN a2enmod rewrite
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies first (better caching)
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

# Copy project files
COPY . .

# Finalise composer
RUN composer dump-autoload --optimize

# Install dusk and behat
RUN composer require --dev laravel/dusk --no-interaction

# Create startup script
RUN echo '#!/bin/bash\n\
# Fix permissions on mounted volumes\n\
echo "Fixing storage permissions..."\n\
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true\n\
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true\n\
\n\
# Create storage link if it doesnt exist\n\
if [ ! -L /var/www/html/public/storage ]; then\n\
    php artisan storage:link\n\
fi\n\
\n\
# Create necessary directories\n\
mkdir -p /var/www/html/public/storage/workers/photos\n\
mkdir -p /var/www/html/public/storage/companies/logos\n\
\n\
# Fix permissions on public storage\n\
chown -R www-data:www-data /var/www/html/public/storage 2>/dev/null || true\n\
chmod -R 775 /var/www/html/public/storage 2>/dev/null || true\n\
\n\
echo "Starting Apache..."\n\
# Start Apache in foreground\n\
apache2-foreground' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Set permissions (these will be overwritten by volumes, but good for base image)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

# Use the startup script
CMD ["/usr/local/bin/start.sh"]