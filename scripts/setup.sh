#!/bin/bash

# Script de configuración inicial para Laravel 10 API
# Este script debe ejecutarse desde la raíz del proyecto

# Obtener el directorio del script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

# Cambiar al directorio raíz del proyecto
cd "$PROJECT_ROOT" || exit 1

echo "🔧 Configurando Laravel 10 para API-first..."

# Copiar .env.example a .env si no existe
if [ ! -f .env ]; then
    echo "📋 Copiando .env.example a .env..."
    cp .env.example .env
fi

# Configurar base de datos PostgreSQL
echo "🗄️  Configurando PostgreSQL..."
sed -i.bak 's/^DB_CONNECTION=.*/DB_CONNECTION=pgsql/' .env
sed -i.bak 's/^DB_HOST=.*/DB_HOST=postgres/' .env
sed -i.bak 's/^DB_PORT=.*/DB_PORT=5432/' .env
sed -i.bak 's/^DB_DATABASE=.*/DB_DATABASE=products_db/' .env
sed -i.bak 's/^DB_USERNAME=.*/DB_USERNAME=products_user/' .env
sed -i.bak 's/^DB_PASSWORD=.*/DB_PASSWORD=products_password/' .env

# Configurar timezone
echo "🕐 Configurando timezone..."
sed -i.bak 's/^APP_TIMEZONE=.*/APP_TIMEZONE=UTC/' .env || echo "APP_TIMEZONE=UTC" >> .env

# Generar APP_KEY si no existe
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "🔑 Generando APP_KEY..."
    docker-compose exec app php artisan key:generate
else
    echo "✅ APP_KEY ya está configurado"
fi

# Limpiar archivos de backup
rm -f .env.bak

echo "✅ Configuración completada!"
echo ""
echo "📝 Próximos pasos:"
echo "   1. Revisa el archivo .env y ajusta según sea necesario"
echo "   2. Ejecuta: docker-compose up -d"
echo "   3. Ejecuta: docker-compose exec app php artisan migrate"
