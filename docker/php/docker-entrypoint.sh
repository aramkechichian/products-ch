#!/bin/bash
set -e

# Función para configurar permisos
setup_permissions() {
    if [ -d "/var/www/html" ]; then
        echo "Configurando permisos para Laravel..."
        
        # Crear directorios si no existen
        mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
        mkdir -p /var/www/html/storage/logs
        mkdir -p /var/www/html/bootstrap/cache
        
        # Configurar permisos
        # Si ejecutamos como root, cambiar ownership y permisos
        if [ "$(id -u)" = "0" ]; then
            chown -R laravel:laravel /var/www/html/storage 2>/dev/null || true
            chown -R laravel:laravel /var/www/html/bootstrap/cache 2>/dev/null || true
            chmod -R 775 /var/www/html/storage 2>/dev/null || true
            chmod -R 775 /var/www/html/bootstrap/cache 2>/dev/null || true
        fi
    fi
}

# Ejecutar configuración de permisos
setup_permissions

# Si ejecutamos como root, cambiar al usuario laravel antes de ejecutar el comando
if [ "$(id -u)" = "0" ]; then
    exec gosu laravel "$@"
else
    exec "$@"
fi
