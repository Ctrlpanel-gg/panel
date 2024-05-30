#!/bin/bash

# Log directory
LOG_DIR="/var/www/html/storage/logs"

echo "Starting script..."

echo "Clearing log file..."
# clean all logs in log directory
if [ -n "$LOG_DIR" ]; then
    rm -f "$LOG_DIR/startup-script.log"
fi

# Check if log directory exists
if [ ! -d "$LOG_DIR" ]; then
    echo "Warning: Log directory does not exist (maybe first install ?). Logging disabled until restart."
    LOG_DIR=""
fi

# Function to log messages
log_message() {
    if [ -n "$LOG_DIR" ]; then
        echo "$1" >> "$LOG_DIR/startup-script.log"
    fi
    echo "$1"
}

# Check if project folder is empty.
if [ -z "$(ls -A /var/www/html)" ]; then
    log_message "Warning: project folder is empty. Copying default files..."
    # Copy everything from /var/default to /var/www/html
    cp -nr /var/default/. /var/www/html   # Use -n to avoid overwriting existing files
    chown -R laravel:laravel /var/www/html/
    chmod -R 755 /var/www/html
fi

#chmod -R 755 /var/www/html
#chmod -R 777 $LOG_DIR

# Check and copy default Nginx configuration if not exists
if [ ! -f "/etc/nginx/conf.d/default.conf" ]; then
    log_message "Warning: Nginx configuration not found. Copying default configuration..."
    cp -n /var/default/docker/standalone/nginx/default.conf /etc/nginx/conf.d/default.conf
fi

# Check and execute composer install if composer.json is present and there's no vendor directory
if [ -f "/var/www/html/composer.json" ] && [ ! -d "/var/www/html/vendor" ]; then
    log_message "Warning: Composer dependencies not found. Running composer install..."
    cd /var/www/html || exit
    composer install --no-dev --optimize-autoloader
    cd - || exit
fi

# Start the queue worker service
log_message "Starting the queue worker service..."
runuser -u laravel -- php /var/www/html/artisan queue:work --sleep=3 --tries=3 &

# Start Nginx
log_message "Starting Nginx..."
service nginx start

# Start PHP-FPM
log_message "Starting PHP-FPM..."
php-fpm -F
