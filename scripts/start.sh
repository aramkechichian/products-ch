#!/bin/bash

# Script para levantar el servidor de desarrollo
# Este script debe ejecutarse desde la raíz del proyecto

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

cd "$PROJECT_ROOT" || exit 1

echo "🚀 Levantando servidor de desarrollo..."
echo ""

# Verificar si .env existe
if [ ! -f .env ]; then
    echo "⚠️  Archivo .env no encontrado. Ejecutando setup..."
    ./scripts/setup.sh
fi

# Levantar contenedores
echo "📦 Levantando contenedores Docker..."
docker-compose up -d

# Esperar a que los servicios estén listos
echo "⏳ Esperando a que los servicios estén listos..."
sleep 5

# Verificar si Swagger está instalado y generar documentación
if docker-compose exec -T app composer show darkaonline/l5-swagger > /dev/null 2>&1; then
    echo "📚 Generando documentación Swagger..."
    docker-compose exec -T app php artisan l5-swagger:generate > /dev/null 2>&1 || echo "⚠️  Swagger no configurado aún. Ejecuta: ./scripts/install-swagger.sh"
fi

# Esperar a que los servicios estén listos
echo "⏳ Esperando a que los servicios estén listos..."
sleep 5

# Verificar estado
echo ""
echo "📊 Estado de los contenedores:"
docker-compose ps

echo ""
echo "✅ Servidor levantado!"
echo ""
echo "🌐 Acceso a la API:"
echo "   - API Base:      http://localhost:8080"
echo "   - API V1:        http://localhost:8080/api/v1"
echo "   - Health:        http://localhost:8080/"
echo "   - Swagger UI:     http://localhost:8080/api/documentation"
echo ""
echo "📝 Endpoints disponibles:"
echo "   POST   http://localhost:8080/api/v1/auth/register"
echo "   POST   http://localhost:8080/api/v1/auth/login"
echo "   GET    http://localhost:8080/api/v1/auth/me (requiere token)"
echo "   POST   http://localhost:8080/api/v1/auth/logout (requiere token)"
echo "   POST   http://localhost:8080/api/v1/auth/logout-all (requiere token)"
echo ""
echo "📋 Ver logs:"
echo "   docker-compose logs -f"
echo ""
echo "🛑 Detener servidor:"
echo "   docker-compose down"
