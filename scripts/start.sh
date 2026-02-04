#!/bin/bash
set -e

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR/.." && pwd )"
cd "$PROJECT_ROOT"

echo "🚀 Levantando entorno de desarrollo"
echo ""

# .env
if [ ! -f .env ]; then
  echo "⚠️  .env no encontrado"
  exit 1
fi

# Docker
docker-compose up -d

# Esperar app healthy
echo "⏳ Esperando contenedor app..."
until docker inspect --format='{{.State.Health.Status}}' products-api-app 2>/dev/null | grep -q healthy; do
  sleep 2
done

echo "✅ App healthy"

# Composer
echo "📦 Verificando dependencias..."
if ! docker-compose exec -T app test -f vendor/autoload.php 2>/dev/null; then
  echo "📦 Instalando dependencias..."
  INSTALL_OUTPUT=$(docker-compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader 2>&1) || true
  echo "$INSTALL_OUTPUT"
  if echo "$INSTALL_OUTPUT" | grep -q "compatible set of packages"; then
    echo "⚠️  Problema de compatibilidad detectado. Actualizando dependencias..."
    docker-compose exec -T app composer update --no-interaction --prefer-dist --with-all-dependencies
  fi
fi

# Swagger
if ! docker-compose exec -T app test -d vendor/darkaonline/l5-swagger 2>/dev/null; then
  echo "📦 Instalando Swagger..."
  docker-compose exec -T app composer require darkaonline/l5-swagger --no-interaction
  docker-compose exec -T app php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --tag="l5-swagger-config" 2>/dev/null || true
fi

# Migraciones
echo "🗄️  Ejecutando migraciones..."
docker-compose exec -T app php artisan migrate --force 2>/dev/null || echo "⚠️  Error ejecutando migraciones"

# Swagger
echo "📚 Generando documentación Swagger..."
docker-compose exec -T app php artisan config:clear 2>/dev/null || true
docker-compose exec -T app php artisan l5-swagger:generate 2>/dev/null || true

echo ""
echo "✅ Proyecto listo"
echo "🌐 API:     http://localhost:8080"
echo "📘 Swagger: http://localhost:8080/api/documentation"
echo ""

docker-compose logs -f
