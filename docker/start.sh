#!/bin/sh
set -e

# 网络工具已在Dockerfile中安装

# 添加pnpm store路径
curl -fsSL https://get.pnpm.io/install.sh | SHELL=sh env -
export PATH="$PATH:$HOME/.local/share/pnpm"

# Wait for Redis to be ready
until nc -z -v -w30 redis 6379; do
  echo "Waiting for Redis connection..."
  sleep 1
done
echo "Redis is ready!"

# Set development environment
export APP_ENV=local

# Generate or use application key without modifying .env
if [ -z "$APP_KEY" ]; then
  # If APP_KEY is not set, generate one and export it
  echo "Generating application key..."
  APP_KEY=$(php artisan key:generate --show --no-interaction)
  export APP_KEY
  echo "Generated key: $APP_KEY"
else
  echo "Using existing APP_KEY from environment"
fi

# 智能安装Telescope
if [ "$APP_ENV" != "production" ]; then
  # 检查Telescope是否已安装
  if ! grep -q "laravel/telescope" composer.json; then
    echo "Installing Telescope package..."
    composer require --dev laravel/telescope
    php artisan telescope:install
  else
    echo "Telescope already installed, skipping installation."
  fi
fi

# Run database migrations
php artisan migrate --force

# Clear and cache configuration with environment variables in effect
php artisan config:clear
php artisan config:cache || echo "Config caching failed, continuing anyway..."
php artisan route:cache || echo "Route caching failed, continuing anyway..."
php artisan view:cache || echo "View caching failed, continuing anyway..."

# Ensure storage directory permissions are correct
chown -R www-data:www-data /var/www/html/storage

# Start PHP-FPM
php-fpm 