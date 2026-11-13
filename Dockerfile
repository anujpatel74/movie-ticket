# ========================================================
# CINEMA BOOKING - Production Dockerfile for Railway
# Base: Official PHP 8.2 with Apache (Debian Bookworm)
# ========================================================

FROM php:8.2-apache

# Set non-interactive debian frontend during build
ENV DEBIAN_FRONTEND=noninteractive

# Install essential system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    libcurl4-openssl-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    sed \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions (PDO MySQL, MySQLi, cURL, OPcache)
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    curl \
    opcache

# --------------------------------------------------------
# APACHE MPM & MODULE CONFIGURATION
# Fixes AH00534: "More than one MPM loaded" permanently
# --------------------------------------------------------
# 1. Guarantee exactly one MPM is enabled (mpm_prefork for PHP mod_php)
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load \
          /etc/apache2/mods-enabled/mpm_*.conf && \
    a2dismod -f mpm_event mpm_worker 2>/dev/null || true && \
    a2enmod mpm_prefork

# 2. Enable essential Apache modules for routing & security
RUN a2enmod rewrite headers expires

# 3. Suppress FQDN ServerName notice & allow .htaccess overrides
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# --------------------------------------------------------
# PRODUCTION PHP INI CONFIGURATION
# --------------------------------------------------------
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

# Verify Apache configuration at build time
RUN apache2ctl configtest

# Set working directory
WORKDIR /var/www/html

# Copy application files into container
COPY . /var/www/html/

# Copy and set execution permissions on entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Ensure correct file permissions for Apache www-data user
RUN chown -R www-data:www-data /var/www/html

# Default port documentation
EXPOSE 80

# Launch through dynamic port entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
