#!/bin/bash
set -e

# ========================================================
# CINEMA BOOKING - Railway Container Entrypoint
# Dynamically binds Apache to Railway's $PORT and ensures
# exactly one MPM is loaded to prevent AH00534.
# ========================================================

# 1. Enforce single MPM configuration (Permanent Fix for AH00534)
MPM_LOAD_COUNT=$(find /etc/apache2/mods-enabled/ -name "mpm_*.load" 2>/dev/null | wc -l)
if [ "$MPM_LOAD_COUNT" -ne 1 ]; then
    echo "[ENTRYPOINT] Detected $MPM_LOAD_COUNT MPM modules loaded. Enforcing exactly one (mpm_prefork)..."
    rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf
    a2dismod -f mpm_event mpm_worker 2>/dev/null || true
    a2enmod mpm_prefork
fi

# 2. Dynamic Port Binding (Railway injects $PORT, fallback to 80)
APP_PORT="${PORT:-80}"
echo "[ENTRYPOINT] Binding Apache to 0.0.0.0:${APP_PORT}..."

# Update ports.conf to listen on 0.0.0.0:${APP_PORT}
if [ -f /etc/apache2/ports.conf ]; then
    sed -i "s/Listen .*/Listen 0.0.0.0:${APP_PORT}/g" /etc/apache2/ports.conf
fi

# Update default VirtualHost to listen on *:${APP_PORT}
if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    sed -i "s/<VirtualHost \*:[0-9]\+>/<VirtualHost \*:${APP_PORT}>/g" /etc/apache2/sites-available/000-default.conf
fi

# Ensure AllowOverride All on /var/www/html so .htaccess rules are executed
sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# 3. Verify Apache Configuration syntax before startup
echo "[ENTRYPOINT] Running Apache syntax verification (apache2ctl configtest)..."
if ! apache2ctl configtest; then
    echo "[ENTRYPOINT FATAL] Apache configuration test failed! Exiting."
    exit 1
fi
echo "[ENTRYPOINT] Apache syntax OK."

# 4. Start Apache web server in foreground
echo "[ENTRYPOINT] Launching Apache in foreground mode on port ${APP_PORT}..."
exec "$@"
