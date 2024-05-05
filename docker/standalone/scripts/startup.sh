#!/bin/bash

# Check if /var/www/html is empty or .env file doesn't exist
if [ -z "$(ls -A /var/www/html)" ] || [ ! -f "/var/www/html/.env" ]; then
    # Copy everything from /var/default to /var/www/html
    cp -nr /var/default/. /var/www/html   # Use -n to avoid overwriting existing files

    # Copy default Nginx configuration
    cp -n /var/default/docker/standalone/nginx/default.conf /etc/nginx/conf.d/default.conf

    # Execute composer install if composer.json is present and there's no vendor directory
    if [ -f "/var/www/html/composer.json" ] && [ ! -d "/var/www/html/vendor" ]; then
        cd /var/www/html
        composer install --no-dev --optimize-autoloader
        cd -
    fi
fi

# Start Nginx
service nginx start

# Start PHP-FPM
php-fpm -F
