# ========================================================
# CINEMA BOOKING - Production Dockerfile for Railway
# Base: PHP 8.2 + Apache
# ========================================================

FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install and configure PHP extensions (pdo, pdo_mysql, curl, opcache)
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    curl \
    opcache

# Enable essential Apache modules
RUN a2enmod rewrite headers expires

# Production PHP settings
RUN echo "display_errors = Off\n\
display_startup_errors = Off\n\
log_errors = On\n\
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT\n\
session.cookie_httponly = 1\n\
session.cookie_samesite = Lax\n\
session.use_strict_mode = 1\n\
session.use_only_cookies = 1\n\
expose_php = Off\n\
memory_limit = 256M\n\
upload_max_filesize = 10M\n\
post_max_size = 12M\n" > /usr/local/etc/php/conf.d/cinema-production.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Copy and setup entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set correct ownership for Apache
RUN chown -R www-data:www-data /var/www/html

# Default port documentation
EXPOSE 80

# Execute entrypoint to dynamically bind Railway's $PORT
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

