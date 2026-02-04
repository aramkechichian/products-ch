# Products API - Laravel 10

API REST profesional construida con Laravel 10+, Docker, PostgreSQL, Sanctum y Swagger.

## 🚀 Requisitos Previos

- Docker Desktop (o Docker + Docker Compose)
- Git

## 📦 Instalación

### 1. Clonar el repositorio (si aplica)
```bash
git clone <repository-url>
cd products-api
```

### 2. Configurar variables de entorno
```bash
cp .env.example .env
```

O ejecuta el script de configuración automática:
```bash
./scripts/setup.sh
```

Este script configurará automáticamente:
- Conexión a PostgreSQL
- APP_KEY
- Timezone (UTC)
- Variables de entorno necesarias

### 3. Levantar los contenedores
```bash
docker-compose up -d
```

### 4. Generar clave de aplicación (si no usaste setup.sh)
```bash
docker-compose exec app php artisan key:generate
```

### 5. Configurar permisos (se hace automáticamente al iniciar, pero puedes ejecutarlo manualmente)
```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R laravel:laravel storage bootstrap/cache
```

### 6. Ejecutar migraciones
```bash
docker-compose exec app php artisan migrate
```

### 7. Instalar Swagger (Opcional pero recomendado)
```bash
./scripts/install-swagger.sh
```

Esto instalará y configurará l5-swagger para documentación automática de la API.

## 🛠️ Comandos Útiles

### Levantar servicios
```bash
docker-compose up -d
```

### Detener servicios
```bash
docker-compose down
```

### Ver logs
```bash
# Todos los servicios
docker-compose logs -f

# Servicio específico
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f postgres
```

### Ejecutar comandos Artisan
```bash
docker-compose exec app php artisan <comando>
```

### Ejecutar Composer
```bash
docker-compose exec app composer <comando>
```

### Acceder al contenedor PHP
```bash
docker-compose exec app bash
```

### Acceder a PostgreSQL
```bash
docker-compose exec postgres psql -U products_user -d products_db
```

### Reconstruir contenedores
```bash
docker-compose build --no-cache
docker-compose up -d
```

## 🌐 Acceso

- **API**: http://localhost:8080
- **API Endpoints**: http://localhost:8080/api
- **API V1**: http://localhost:8080/api/v1
- **Swagger UI**: http://localhost:8080/api/documentation
- **PostgreSQL**: localhost:5432

## 📁 Estructura del Proyecto

```
products-api/
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   ├── php.ini
│   │   └── docker-entrypoint.sh
│   └── postgres/
│       └── init.sql
├── docker-compose.yml
├── Dockerfile
├── .env.example
├── scripts/
│   └── setup.sh
└── README.md
```

## 🔧 Servicios Docker

- **app**: PHP 8.2-FPM con extensiones necesarias
- **nginx**: Servidor web Nginx
- **postgres**: Base de datos PostgreSQL 15
- **composer**: Servicio para ejecutar Composer (perfil: tools)

## 📝 Notas

### Configuración API-First
- **Modo API**: La aplicación está configurada como API-first
- **CORS habilitado**: Configurado para permitir requests desde frontend
- **Rutas web mínimas**: Solo endpoint de estado en `/`
- **Rutas API**: Todas las funcionalidades principales en `/api/*`
- **Sanctum listo**: Middleware y configuración preparados para autenticación

### Hot Reload
- **OPcache deshabilitado** para desarrollo, permitiendo cambios en tiempo real
- Los cambios en archivos PHP se reflejan inmediatamente sin reiniciar contenedores
- Los volúmenes están configurados para sincronización bidireccional

### Permisos
- Los permisos de `storage/` y `bootstrap/cache/` se configuran automáticamente al iniciar el contenedor
- El script `docker-entrypoint.sh` se ejecuta en cada inicio para asegurar permisos correctos
- Si tienes problemas de permisos, ejecuta manualmente:
  ```bash
  docker-compose exec app chmod -R 775 storage bootstrap/cache
  ```

### Preparación para Sanctum y Swagger
- **Extensiones PHP instaladas**: `pdo_pgsql`, `pgsql`, `mbstring`, `exif`, `pcntl`, `bcmath`, `gd`, `zip`
- **XML support**: Incluido para Swagger/OpenAPI
- **Variables de entorno**: Configuradas en `.env.example` para Sanctum y CORS
- **CORS con credenciales**: Habilitado para soportar Sanctum stateful authentication

### Base de Datos
- **PostgreSQL 15**: Configurado como base de datos por defecto
- **Conexión**: `postgres:5432` desde el contenedor
- **Credenciales**: Ver `.env.example` para valores por defecto
- El usuario y grupo dentro del contenedor se configuran automáticamente
- PostgreSQL crea la base de datos automáticamente al iniciar

## 🎯 Estado del Proyecto

✅ Laravel 10 instalado y configurado
✅ Docker y servicios configurados
✅ PostgreSQL configurado como base de datos por defecto
✅ Modo API-first configurado
✅ CORS configurado para desarrollo
✅ Sanctum preparado (pendiente instalación)
✅ Swagger preparado (pendiente instalación)
