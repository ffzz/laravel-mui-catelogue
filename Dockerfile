FROM php:8.3-fpm-alpine AS php

# Install PHP extensions and dependencies
RUN apk add --no-cache \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    sqlite \
    oniguruma-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.js stage
FROM node:22-alpine AS node

# Set working directory
WORKDIR /var/www/html

# Final stage
FROM php AS app

# Install Node.js and npm
COPY --from=node /usr/local/bin/node /usr/local/bin/
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Copy project files
COPY . /var/www/html/

# Create storage directories and set permissions
RUN mkdir -p /var/www/html/storage/app \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    && chown -R www-data:www-data /var/www/html/storage

# Copy dotenv file for build time configuration
COPY .env.example /var/www/html/.env

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-scripts

# Default to development environment
ENV APP_ENV=local
ENV APP_KEY=base64:4tszo/3aFh4oJXamgq7o1lAvcQTKmslejKGH7oi5MSY=

# Install PNPM and Node.js dependencies
RUN npm install -g pnpm@10.7.0 \
    && pnpm install \
    && pnpm run build

# Add startup script
COPY docker/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

# Set default command
CMD ["start"] 