FROM php:8.3-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libxml2-dev libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql zip gd xml bcmath \
    && apt-get clean

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose port
EXPOSE 8080

# Start command
CMD php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan db:seed --class=RolePermissionSeeder --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php -S 0.0.0.0:8080 -t public
