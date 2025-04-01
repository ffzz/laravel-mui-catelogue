FROM php:8.3-fpm AS php

# Install PHP extensions and dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    sqlite3 \
    libsqlite3-dev \
    libonig-dev \
    autoconf \
    g++ \
    make \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && pecl install redis && docker-php-ext-enable redis \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.js stage
FROM node:22 AS node

# Install specified version of pnpm
RUN npm install -g pnpm@10.7.0

# Verify installation
RUN pnpm --version

# Set working directory
WORKDIR /var/www/html

# Final stage
FROM php AS app

# Install Node.js and npm/npx
COPY --from=node /usr/local/bin/node /usr/local/bin/
COPY --from=node /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# Install pnpm (full copy of pnpm directory)
COPY --from=node /usr/local/lib/node_modules/pnpm /usr/local/lib/node_modules/pnpm
RUN ln -s /usr/local/lib/node_modules/pnpm/bin/pnpm.cjs /usr/local/bin/pnpm \
    && ln -s /usr/local/lib/node_modules/pnpm/bin/pnpx.cjs /usr/local/bin/pnpx

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
RUN pnpm install --frozen-lockfile \
    && pnpm run build

# Add startup script
COPY docker/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

# Set default command
CMD ["start"]

# 在app阶段添加网络工具
RUN apt-get update && apt-get install -y \
    netcat-openbsd \
    iputils-ping \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Fix permissions for VS Code Server
RUN mkdir -p /var/www/.cursor-server /var/www/.vscode-remote \
    && chown -R www-data:www-data /var/www/.cursor-server /var/www/.vscode-remote \
    && chmod -R 755 /var/www/.cursor-server /var/www/.vscode-remote 