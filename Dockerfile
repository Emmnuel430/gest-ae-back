# Dockerfile

FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    libzip-dev \
    libpq-dev \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# ✅ On copie TOUT avant d’installer
COPY . .

# ✅ Installer les dépendances Laravel après avoir copié tous les fichiers (y compris artisan)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Permissions correctes
RUN chmod -R 755 /var/www && chown -R www-data:www-data /var/www

# Nettoyage Laravel
RUN php artisan config:clear && \
    php artisan route:clear && \
    php artisan view:clear

EXPOSE 8000

CMD ["php", "artisan", "migrate", '--force', "&&", "php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
