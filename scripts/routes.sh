#!/bin/bash

# Script para ver las rutas de la API
# Este script debe ejecutarse desde la raíz del proyecto

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

cd "$PROJECT_ROOT" || exit 1

echo "🔍 Listando rutas de la API..."
echo ""

# Ver todas las rutas
if [ "$1" = "--all" ] || [ "$1" = "-a" ]; then
    echo "📋 Todas las rutas:"
    docker compose exec app php artisan route:list
elif [ "$1" = "--api" ] || [ "$1" = "-api" ]; then
    echo "📋 Rutas de la API:"
    docker compose exec app php artisan route:list --path=api
elif [ "$1" = "--v1" ] || [ "$1" = "-v1" ]; then
    echo "📋 Rutas de la API V1:"
    docker compose exec app php artisan route:list --path=api/v1
else
    echo "📋 Rutas de la API V1 (por defecto):"
    docker compose exec app php artisan route:list --path=api/v1
    echo ""
    echo "💡 Opciones disponibles:"
    echo "   ./scripts/routes.sh          - Ver rutas V1 (por defecto)"
    echo "   ./scripts/routes.sh --v1     - Ver rutas V1"
    echo "   ./scripts/routes.sh --api    - Ver todas las rutas API"
    echo "   ./scripts/routes.sh --all    - Ver todas las rutas"
fi
