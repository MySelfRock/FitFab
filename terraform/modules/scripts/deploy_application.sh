#!/bin/bash
#
# deploy_application.sh
# Deploy FitFab Laravel application to a server
#
# Usage: ./deploy_application.sh [production|demo]
#

set -e

ENVIRONMENT=${1:-demo}
APP_DIR="/var/www/fitfab"

echo "========================================"
echo "FitFab Application Deployment"
echo "Environment: $ENVIRONMENT"
echo "========================================"

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo "Please run with sudo or as root"
    exit 1
fi

# Backup current application (if exists)
if [ -d "$APP_DIR" ] && [ "$(ls -A $APP_DIR)" ]; then
    BACKUP_DIR="/var/backups/fitfab-$(date +%Y%m%d-%H%M%S)"
    echo "Creating backup at $BACKUP_DIR..."
    mkdir -p /var/backups
    cp -r $APP_DIR $BACKUP_DIR
    echo "Backup created successfully"
fi

# Pull latest code (assuming git is set up)
echo "Pulling latest code..."
cd $APP_DIR
sudo -u www-data git pull origin main

# Install Composer dependencies
echo "Installing Composer dependencies..."
sudo -u www-data composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Install NPM dependencies and build assets
echo "Building frontend assets..."
sudo -u www-data npm install
sudo -u www-data npm run build

# Clear and cache configuration
echo "Optimizing Laravel..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

if [ "$ENVIRONMENT" = "production" ]; then
    sudo -u www-data php artisan config:cache
    sudo -u www-data php artisan route:cache
    sudo -u www-data php artisan view:cache
fi

# Run migrations
echo "Running database migrations..."
sudo -u www-data php artisan migrate --force

# Restart services
echo "Restarting services..."
systemctl restart php8.2-fpm
systemctl restart nginx

# Restart queue workers
if systemctl is-active --quiet supervisor; then
    supervisorctl restart laravel-worker:*
    supervisorctl restart laravel-horizon
fi

# Set correct permissions
echo "Setting permissions..."
chown -R www-data:www-data $APP_DIR
chmod -R 755 $APP_DIR
chmod -R 775 $APP_DIR/storage
chmod -R 775 $APP_DIR/bootstrap/cache

echo "========================================"
echo "Deployment completed successfully!"
echo "========================================"
