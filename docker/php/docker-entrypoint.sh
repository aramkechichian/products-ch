#!/bin/sh
set -e

echo "🔧 Inicializando contenedor Laravel (entrypoint)"

APP_DIR="/var/www/html"

# Crear carpetas necesarias
mkdir -p \
  "$APP_DIR/storage/framework/sessions" \
  "$APP_DIR/storage/framework/views" \
  "$APP_DIR/storage/framework/cache" \
  "$APP_DIR/storage/logs" \
  "$APP_DIR/bootstrap/cache"

# Permisos SOLO donde Laravel escribe
chown -R www-data:www-data \
  "$APP_DIR/storage" \
  "$APP_DIR/bootstrap/cache"

chmod -R 775 \
  "$APP_DIR/storage" \
  "$APP_DIR/bootstrap/cache"


exec "$@"