#!/bin/bash

# Script para instalar y configurar Swagger
# Este script debe ejecutarse desde la raíz del proyecto

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"

cd "$PROJECT_ROOT" || exit 1

echo "📦 Instalando l5-swagger..."

# Instalar el paquete
docker-compose exec app composer require darkaonline/l5-swagger

echo "📋 Publicando configuración de Swagger..."
# Publicar configuración
docker-compose exec app php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

echo "🔧 Generando documentación inicial..."
# Generar documentación
docker-compose exec app php artisan l5-swagger:generate

echo "✅ Swagger instalado y configurado!"
echo ""
echo "🌐 Acceso a Swagger UI:"
echo "   http://localhost:8080/api/documentation"
echo ""
echo "📝 Para regenerar la documentación después de cambios:"
echo "   docker-compose exec app php artisan l5-swagger:generate"
