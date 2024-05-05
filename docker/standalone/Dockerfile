FROM php:8.1-fpm

# Install Nginx and other dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    curl \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql intl zip

# Copy the custom PHP-FPM configuration
ADD ./docker/standalone/php/www.conf /usr/local/etc/php-fpm.d/

# Create directory for application and set ownership
RUN mkdir -p /var/default && \
    groupadd -g 1000 laravel && \
    useradd -u 1000 -g laravel -ms /bin/bash laravel && \
    chown laravel:laravel /var/default

# Copy application files into the container
COPY . /var/default

# Copy Composer binary from Composer image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Nginx
COPY ./docker/standalone/nginx/nginx.conf /etc/nginx/nginx.conf

# Create directory for Nginx logs and set ownership
RUN mkdir -p /var/log/nginx && \
    chown -R laravel:laravel /var/log/nginx

# Expose ports
EXPOSE 80

# Copy startup script
COPY ./docker/standalone/scripts/startup.sh /usr/local/bin/startup-script.sh

# Make startup script executable
RUN chmod +x /usr/local/bin/startup-script.sh

# Start the startup script
CMD ["/usr/local/bin/startup-script.sh"]
