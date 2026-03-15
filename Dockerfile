FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libxml2-dev \
    libsqlite3-dev libicu-dev g++ \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql zip gd xml bcmath intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD php artisan key:generate --force && \
    php artisan migrate --force && \
    php artisan db:seed --class=RolePermissionSeeder --force && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php -S 0.0.0.0:8080 -t public
