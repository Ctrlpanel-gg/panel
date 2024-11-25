FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    nginx \
    nano \
    curl \
    git \
    libcurl4-openssl-dev \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN pecl install redis \
    && docker-php-ext-enable redis \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo \
        pdo_mysql \
        intl \
        zip \
        gd \
        bcmath \
    && rm -rf /tmp/* /var/cache/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create directory for application
RUN mkdir -p /var/default /var/www/html

# Create user and group for Laravel
RUN groupadd -g 1000 laravel \
    && useradd -u 1000 -g laravel -ms /bin/bash laravel \
    && chown -R laravel:laravel /var/default /var/www/html

# Copy application files
COPY --chown=laravel:laravel . /var/default

# Copy PHP-FPM configuration
COPY --chown=laravel:laravel ./docker/standalone/php/www.conf /usr/local/etc/php-fpm.d/

# Copy Nginx configuration
COPY --chown=laravel:laravel ./docker/standalone/nginx/nginx.conf /etc/nginx/nginx.conf
# Create directory for Nginx logs
RUN mkdir -p /var/log/nginx && chown -R laravel:laravel /var/log/nginx

# Expose ports
EXPOSE 80 443

# Copy startup script
COPY --chown=laravel:laravel ./docker/standalone/scripts/startup.sh /usr/local/bin/startup-script.sh
# Make startup script executable
RUN chmod +x /usr/local/bin/startup-script.sh

# Set the working directory
WORKDIR /var/www/html

# Start the startup script
CMD ["/usr/local/bin/startup-script.sh"]
