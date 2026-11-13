#!/bin/bash
set -e

# Railway dynamic port binding (defaults to 80 if PORT is unset)
APP_PORT="${PORT:-80}"

echo "[STARTUP] Configuring Apache to bind on port ${APP_PORT}..."

# Update Apache ports configuration
sed -i "s/Listen 80/Listen ${APP_PORT}/g" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${APP_PORT}>/g" /etc/apache2/sites-available/000-default.conf

# Ensure AllowOverride All on /var/www/html for .htaccess support
sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

echo "[STARTUP] Starting Cinema Booking application server..."

exec "$@"

