# Products API - Laravel 10

API REST profesional construida con Laravel 10+, Docker, PostgreSQL, Sanctum y Swagger.

## 📋 Tabla de Contenidos

- [Requisitos Previos](#-requisitos-previos)
- [Instalación](#-instalación)
- [Comandos Útiles](#-comandos-útiles)
- [Acceso a la API](#-acceso-a-la-api)
- [Autenticación](#-autenticación)
- [Endpoints Requeridos](#-endpoints-requeridos)
- [Bonus Tracks](#-bonus-tracks)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Configuración](#-configuración)

## 🚀 Requisitos Previos

- Docker Desktop (o Docker + Docker Compose)
- Git

## 📦 Instalación

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd products-api
```

### 2. Configurar variables de entorno

**Opción A: Script automático (recomendado)**
```bash
./scripts/setup.sh
```

Este script configurará automáticamente:
- Conexión a PostgreSQL
- APP_KEY
- Timezone (UTC)
- Variables de entorno necesarias

**Opción B: Manual**
```bash
cp .env.example .env
docker-compose exec app php artisan key:generate
```

### 3. Levantar el proyecto

**Opción A: Script automático (recomendado)**
```bash
./scripts/start.sh
```

Este script:
- Levanta los contenedores Docker
- Instala dependencias de Composer
- Ejecuta migraciones
- Instala y configura Swagger
- Genera la documentación de la API

**Opción B: Manual**
```bash
# Levantar contenedores
docker-compose up -d

# Instalar dependencias
docker-compose exec app composer install

# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Instalar Swagger (opcional)
docker-compose exec app composer require darkaonline/l5-swagger
docker-compose exec app php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
docker-compose exec app php artisan l5-swagger:generate
```

## 🛠️ Comandos Útiles

### Gestión de Docker

**Levantar servicios:**
```bash
docker-compose up -d
```

**Detener servicios:**
```bash
docker-compose down
```

**Detener y eliminar volúmenes (limpieza completa):**
```bash
docker-compose down -v
```

**Reconstruir contenedores:**
```bash
docker-compose build --no-cache
docker-compose up -d
```

**Ver logs:**
```bash
# Todos los servicios
docker-compose logs -f

# Servicio específico
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f postgres
```

### Comandos Artisan

**Ejecutar cualquier comando Artisan:**
```bash
docker-compose exec app php artisan <comando>
```

**Ejemplos:**
```bash
# Ver rutas disponibles
docker-compose exec app php artisan route:list

# Limpiar caché
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Crear migración
docker-compose exec app php artisan make:migration nombre_migracion

# Crear modelo
docker-compose exec app php artisan make:model NombreModelo
```

### Comandos Composer

**Instalar dependencias:**
```bash
docker-compose exec app composer install
```

**Actualizar dependencias:**
```bash
docker-compose exec app composer update
```

**Agregar nueva dependencia:**
```bash
docker-compose exec app composer require nombre/paquete
```

### Acceso a Contenedores

**Acceder al contenedor PHP:**
```bash
docker-compose exec app bash
```

**Acceder a PostgreSQL:**
```bash
docker-compose exec postgres psql -U products_user -d products_db
```

### Tests

**Ejecutar todos los tests:**
```bash
docker-compose exec app composer test
# o
docker-compose exec app php artisan test
```

**Ejecutar tests específicos:**
```bash
docker-compose exec app php artisan test --filter NombreTest
```

**Ejecutar tests con script:**
```bash
./scripts/run_tests.sh
```

### Swagger

**Generar documentación Swagger:**
```bash
docker-compose exec app php artisan l5-swagger:generate
```

## 🌐 Acceso a la API

- **API Base**: http://localhost:8080
- **API V1**: http://localhost:8080/api/v1
- **Swagger UI**: http://localhost:8080/api/documentation
- **PostgreSQL**: localhost:5432

## 🔐 Autenticación

La API utiliza **Laravel Sanctum** para autenticación mediante Bearer Tokens.

### Flujo de Autenticación

1. **Registro o Login** para obtener un token
2. **Incluir el token** en todas las peticiones protegidas
3. **Header requerido**: `Authorization: Bearer {token}`

### Endpoints de Autenticación

**Registro (público):**
```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Rambo",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Login (público):**
```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "juan@example.com",
  "password": "password123"
}
```

**Respuesta de Login/Register:**
```json
{
  "success": true,
  "message": "Logged in successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "Juan Pérez",
      "email": "juan@example.com"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
  }
}
```

**Usar el token:**
```http
GET /api/v1/products
Authorization: Bearer 1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

## 📍 Endpoints Requeridos

### 1. GET /api/v1/products

**Descripción:** Obtiene la lista completa de productos con su información de moneda.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/products`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Lógica:**
- Obtiene todos los productos de la base de datos
- Incluye la relación `currency` (moneda) de cada producto
- Ordena los productos por nombre (ascendente)
- Retorna una lista completa sin paginación

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Products retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Laptop",
      "description": "High-performance laptop",
      "price": 1299.99,
      "currency": {
        "id": 1,
        "name": "US Dollar",
        "symbol": "USD",
        "exchange_rate": 1.0000
      },
      "currency_id": 1,
      "tax_cost": 100.00,
      "manufacturing_cost": 800.00,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

**Errores:**
- `401 Unauthorized`: Token inválido o no proporcionado

---

### 2. POST /api/v1/products

**Descripción:** Crea un nuevo producto en el sistema.

**Autenticación:** Requerida (Bearer Token)

**Método:** `POST`

**URL:** `/api/v1/products`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "name": "Laptop",
  "description": "High-performance laptop for professionals",
  "price": 1299.99,
  "currency_id": 1,
  "tax_cost": 100.00,
  "manufacturing_cost": 800.00,
  "create_product_prices": false
}
```

**Campos:**
- `name` (requerido): Nombre del producto (string, máx. 255 caracteres)
- `description` (requerido): Descripción del producto (string)
- `price` (requerido): Precio del producto (número, mínimo 0)
- `currency_id` (requerido): ID de la moneda base del producto (debe existir en la tabla `currencies`)
- `tax_cost` (opcional): Costo de impuestos (número, mínimo 0)
- `manufacturing_cost` (opcional): Costo de manufactura (número, mínimo 0)
- `create_product_prices` (opcional): Si es `true`, crea automáticamente precios en todas las monedas disponibles (excepto la moneda base). Por defecto: `false`

**Lógica:**
1. Valida que todos los campos requeridos estén presentes y sean válidos
2. Valida que `currency_id` exista en la tabla `currencies`
3. Crea el producto en la base de datos
4. Si no existe ningún `ProductPrice` para este producto, crea automáticamente uno con el mismo precio del producto (sin multiplicar por `exchange_rate`)
5. Si `create_product_prices` es `true`, crea precios en todas las monedas disponibles (excepto la moneda base), calculando: `product.price * currency.exchange_rate`
6. Registra el evento en el log de eventos
7. Retorna el producto creado con su información de moneda

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "name": "Laptop",
    "description": "High-performance laptop for professionals",
    "price": 1299.99,
    "currency": {
      "id": 1,
      "name": "US Dollar",
      "symbol": "USD",
      "exchange_rate": 1.0000
    },
    "currency_id": 1,
    "tax_cost": 100.00,
    "manufacturing_cost": 800.00,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

**Errores:**
- `401 Unauthorized`: Token inválido o no proporcionado
- `422 Validation Error`: Datos inválidos
  ```json
  {
    "success": false,
    "message": "Validation failed",
    "errors": {
      "name": ["The product name is required."],
      "currency_id": ["The selected currency does not exist."]
    }
  }
  ```

---

### 3. GET /api/v1/products/{id}

**Descripción:** Obtiene un producto específico por su ID.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/products/{id}`

**Parámetros de URL:**
- `id` (requerido): ID del producto (integer)

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Lógica:**
- Busca el producto por ID en la base de datos
- Incluye la relación `currency` (moneda) del producto
- Si el producto no existe, retorna error 404

**Ejemplo de Request:**
```http
GET /api/v1/products/1
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Product retrieved successfully",
  "data": {
    "id": 1,
    "name": "Laptop",
    "description": "High-performance laptop",
    "price": 1299.99,
    "currency": {
      "id": 1,
      "name": "US Dollar",
      "symbol": "USD",
      "exchange_rate": 1.0000
    },
    "currency_id": 1,
    "tax_cost": 100.00,
    "manufacturing_cost": 800.00,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

**Errores:**
- `401 Unauthorized`: Token inválido o no proporcionado
- `404 Not Found`: Producto no encontrado
  ```json
  {
    "success": false,
    "message": "Product not found"
  }
  ```

---

### 4. PUT /api/v1/products/{id}

**Descripción:** Actualiza un producto existente.

**Autenticación:** Requerida (Bearer Token)

**Método:** `PUT`

**URL:** `/api/v1/products/{id}`

**Parámetros de URL:**
- `id` (requerido): ID del producto (integer)

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "name": "Laptop Pro",
  "description": "Updated description",
  "price": 1499.99,
  "currency_id": 1,
  "tax_cost": 120.00,
  "manufacturing_cost": 900.00
}
```

**Campos (todos opcionales, pero al menos uno debe enviarse):**
- `name`: Nombre del producto (string, máx. 255 caracteres)
- `description`: Descripción del producto (string)
- `price`: Precio del producto (número, mínimo 0)
- `currency_id`: ID de la moneda (debe existir en la tabla `currencies` si se envía)
- `tax_cost`: Costo de impuestos (número, mínimo 0)
- `manufacturing_cost`: Costo de manufactura (número, mínimo 0)

**Lógica:**
1. Valida que el producto exista (si no existe, retorna 404)
2. Valida los campos enviados
3. Si se envía `currency_id`, valida que exista en la tabla `currencies`
4. **Si se actualiza el campo `price`**, automáticamente actualiza todos los `ProductPrice` relacionados:
   - Para la moneda base del producto: actualiza el precio al mismo valor (sin multiplicar)
   - Para otras monedas: recalcula el precio usando `nuevo_precio * currency.exchange_rate`
5. Actualiza solo los campos enviados en el request
6. Registra el evento en el log de eventos
7. Retorna el producto actualizado con su información de moneda

**Ejemplo de Request:**
```http
PUT /api/v1/products/1
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Laptop Pro",
  "price": 1499.99
}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "name": "Laptop Pro",
    "description": "High-performance laptop",
    "price": 1499.99,
    "currency": {
      "id": 1,
      "name": "US Dollar",
      "symbol": "USD",
      "exchange_rate": 1.0000
    },
    "currency_id": 1,
    "tax_cost": 100.00,
    "manufacturing_cost": 800.00,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T11:00:00Z"
  }
}
```

**Errores:**
- `401 Unauthorized`: Token inválido o no proporcionado
- `404 Not Found`: Producto no encontrado
- `422 Validation Error`: Datos inválidos

---

### 5. DELETE /api/v1/products/{id}

**Descripción:** Elimina un producto del sistema. Al eliminar un producto, se eliminan automáticamente todos sus precios relacionados (`ProductPrice`) gracias a la configuración de cascade delete en la base de datos.

**Autenticación:** Requerida (Bearer Token)

**Método:** `DELETE`

**URL:** `/api/v1/products/{id}`

**Parámetros de URL:**
- `id` (requerido): ID del producto (integer)

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Lógica:**
1. Valida que el producto exista (si no existe, retorna 404)
2. Elimina el producto de la base de datos
3. **Automáticamente elimina todos los `ProductPrice` relacionados** gracias a la configuración `onDelete('cascade')` en la migración
4. Registra el evento en el log de eventos
5. Retorna mensaje de éxito

**Ejemplo de Request:**
```http
DELETE /api/v1/products/1
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Product deleted successfully",
  "data": null
}
```

**Errores:**
- `401 Unauthorized`: Token inválido o no proporcionado
- `404 Not Found`: Producto no encontrado

---

### 6. GET /api/v1/products/{id}/prices

**Descripción:** Obtiene la lista de precios de un producto en diferentes monedas.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/products/{id}/prices`

**Parámetros de URL:**
- `id` (requerido): ID del producto (integer)

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Lógica:**
- Busca el producto por ID (si no existe, retorna 404)
- Obtiene todos los `ProductPrice` asociados a ese producto
- Incluye la relación `currency` (moneda) de cada precio
- Ordena los precios por fecha de creación (más recientes primero)
- Retorna la lista completa de precios

**Ejemplo de Request:**
```http
GET /api/v1/products/1/prices
Authorization: Bearer {token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "message": "Product prices retrieved successfully",
  "data": [
    {
      "id": 1,
      "product_id": 1,
      "currency": {
        "id": 2,
        "name": "Euro",
        "symbol": "EUR",
        "exchange_rate": 0.85
      },
      "currency_id": 2,
      "price": 1104.99,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    },
    {
      "id": 2,
      "product_id": 1,
      "currency": {
        "id": 1,
        "name": "US Dollar",
        "symbol": "USD",
        "exchange_rate": 1.0000
      },
      "currency_id": 1,
      "price": 1299.99,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

**Nota:** El precio en la moneda base del producto (la misma que `product.currency_id`) se crea automáticamente cuando se crea el producto, con el mismo valor que `product.price` (sin multiplicar por `exchange_rate`).

**Errores:**
- `401 Unauthorized`: Token inválido o no proporcionado
- `404 Not Found`: Producto no encontrado

---

### 7. POST /api/v1/products/{id}/prices

**Descripción:** Crea o actualiza un precio para un producto en una moneda diferente a su moneda base.

**Autenticación:** Requerida (Bearer Token)

**Método:** `POST`

**URL:** `/api/v1/products/{id}/prices`

**Parámetros de URL:**
- `id` (requerido): ID del producto (integer)

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Body (JSON):**
```json
{
  "currency_id": 2
}
```

**Campos:**
- `currency_id` (requerido): ID de la moneda destino (integer, debe existir y ser diferente a la moneda base del producto)

**Lógica:**
1. Valida que el producto exista (si no existe, retorna 404)
2. Valida que `currency_id` exista en la tabla `currencies`
3. Valida que `currency_id` sea diferente a la moneda base del producto (no se puede crear un precio en la misma moneda base)
4. Calcula el precio convertido: `product.price * currency.exchange_rate`
5. Redondea el precio a 2 decimales
6. Si ya existe un precio para este producto en esta moneda, lo actualiza. Si no existe, lo crea
7. Registra el evento en el log de eventos (CREATE o UPDATE según corresponda)
8. Retorna el precio creado/actualizado con su información de moneda

**Ejemplo de Request:**
```http
POST /api/v1/products/1/prices
Authorization: Bearer {token}
Content-Type: application/json

{
  "currency_id": 2
}
```

**Ejemplo:** Si el producto tiene precio base de $1000 USD (currency_id: 1, exchange_rate: 1.0) y queremos crear el precio en EUR (currency_id: 2, exchange_rate: 0.85):
- Precio calculado: $1000 * 0.85 = $850 EUR

**Respuesta Exitosa (201 si se crea, 200 si se actualiza):**
```json
{
  "success": true,
  "message": "Product price created successfully",
  "data": {
    "id": 1,
    "product_id": 1,
    "currency": {
      "id": 2,
      "name": "Euro",
      "symbol": "EUR",
      "exchange_rate": 0.85
    },
    "currency_id": 2,
    "price": 1104.99,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  }
}
```

**Errores:**
- `401 Unauthorized`: Token inválido o no proporcionado
- `404 Not Found`: Producto o moneda no encontrado
- `422 Validation Error`: Datos inválidos
  ```json
  {
    "success": false,
    "message": "Validation failed",
    "errors": {
      "currency_id": ["Cannot create a price in the same currency as the product base currency."]
    }
  }
  ```

---

## 🎁 Bonus Tracks

Endpoints adicionales que agregan valor al challenge.

### Autenticación Adicional

#### GET /api/v1/auth/me

**Descripción:** Obtiene la información del usuario autenticado.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/auth/me`

**Respuesta:**
```json
{
  "success": true,
  "message": "User retrieved successfully",
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "juan@example.com"
  }
}
```

#### POST /api/v1/auth/logout

**Descripción:** Cierra sesión revocando el token actual.

**Autenticación:** Requerida (Bearer Token)

**Método:** `POST`

**URL:** `/api/v1/auth/logout`

#### POST /api/v1/auth/logout-all

**Descripción:** Cierra sesión en todos los dispositivos revocando todos los tokens del usuario.

**Autenticación:** Requerida (Bearer Token)

**Método:** `POST`

**URL:** `/api/v1/auth/logout-all`

---

### Gestión de Monedas (Currencies)

#### GET /api/v1/currencies

**Descripción:** Obtiene la lista de todas las monedas disponibles.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/currencies`

**Respuesta:**
```json
{
  "success": true,
  "message": "Currencies retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "US Dollar",
      "symbol": "USD",
      "exchange_rate": 1.0000,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    }
  ]
}
```

#### POST /api/v1/currencies

**Descripción:** Crea una nueva moneda.

**Autenticación:** Requerida (Bearer Token)

**Método:** `POST`

**URL:** `/api/v1/currencies`

**Body:**
```json
{
  "name": "Euro",
  "symbol": "EUR",
  "exchange_rate": 0.85
}
```

#### GET /api/v1/currencies/{id}

**Descripción:** Obtiene una moneda por ID.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/currencies/{id}`

#### PUT /api/v1/currencies/{id}

**Descripción:** Actualiza una moneda existente.

**Autenticación:** Requerida (Bearer Token)

**Método:** `PUT`

**URL:** `/api/v1/currencies/{id}`

#### DELETE /api/v1/currencies/{id}

**Descripción:** Elimina una moneda (solo si no tiene productos asociados).

**Autenticación:** Requerida (Bearer Token)

**Método:** `DELETE`

**URL:** `/api/v1/currencies/{id}`

---

### Búsqueda Avanzada de Productos

#### GET /api/v1/products/search

**Descripción:** Búsqueda avanzada de productos con múltiples filtros y paginación.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/products/search`

**Query Parameters (todos opcionales):**
- `name`: Búsqueda parcial por nombre (case-insensitive)
- `currency_symbol`: Filtrar por símbolo de moneda (ej: "USD", "EUR")
- `min_price`: Precio mínimo
- `max_price`: Precio máximo
- `min_tax_cost`: Costo de impuestos mínimo
- `max_tax_cost`: Costo de impuestos máximo
- `min_manufacturing_cost`: Costo de manufactura mínimo
- `max_manufacturing_cost`: Costo de manufactura máximo
- `sort_by`: Campo para ordenar (`name`, `price`, `tax_cost`, `manufacturing_cost`, `created_at`, `updated_at`)
- `sort_order`: Orden (`asc` o `desc`)
- `per_page`: Resultados por página (1-100, default: 15)
- `page`: Número de página (default: 1)

**Ejemplo:**
```http
GET /api/v1/products/search?name=laptop&min_price=1000&max_price=2000&sort_by=price&sort_order=desc&per_page=10
Authorization: Bearer {token}
```

**Respuesta (con paginación):**
```json
{
  "success": true,
  "message": "Products found successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Laptop",
        "price": 1299.99,
        ...
      }
    ],
    "current_page": 1,
    "per_page": 10,
    "total": 25,
    "last_page": 3
  }
}
```

---

### Exportación a Excel

#### GET /api/v1/products/export

**Descripción:** Descarga un archivo Excel con todos los productos.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/products/export`

**Respuesta:** Archivo Excel (.xlsx) con columnas:
- ID
- Name
- Description
- Price
- Currency
- Currency Symbol
- Tax Cost
- Manufacturing Cost
- Created At
- Updated At

**Nombre del archivo:** `products_YYYY-MM-DD_HHmmss.xlsx`

---

#### GET /api/v1/product-prices/export

**Descripción:** Descarga un archivo Excel con todos los precios de productos.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/product-prices/export`

**Respuesta:** Archivo Excel (.xlsx) con columnas:
- Product Name
- Currency Name
- Price

**Nombre del archivo:** `product_prices_YYYY-MM-DD_HHmmss.xlsx`

---

### Logs de Eventos

#### GET /api/v1/event-logs

**Descripción:** Obtiene la lista de eventos registrados en el sistema (POST, PUT, DELETE).

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/event-logs`

**Query Parameters (opcionales):**
- `per_page`: Resultados por página (default: 15)
- `page`: Número de página (default: 1)

**Respuesta:**
```json
{
  "success": true,
  "message": "Event logs retrieved successfully",
  "data": {
    "data": [
      {
        "id": 1,
        "user": {
          "id": 1,
          "name": "Juan Pérez",
          "email": "juan@example.com"
        },
        "event_type": "POST",
        "resource_type": "Product",
        "resource_id": 1,
        "endpoint": "/api/v1/products",
        "method": "POST",
        "data": {
          "payload": {
            "name": "Laptop",
            "price": 1299.99
          }
        },
        "ip_address": "192.168.1.1",
        "user_agent": "Mozilla/5.0...",
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4
  }
}
```

#### GET /api/v1/event-logs/{id}

**Descripción:** Obtiene el detalle de un evento específico.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/event-logs/{id}`

#### GET /api/v1/event-logs/export

**Descripción:** Descarga un archivo Excel con los eventos filtrados por rango de fechas.

**Autenticación:** Requerida (Bearer Token)

**Método:** `GET`

**URL:** `/api/v1/event-logs/export`

**Query Parameters (opcionales):**
- `start_date`: Fecha inicio (YYYY-MM-DD)
- `end_date`: Fecha fin (YYYY-MM-DD)

**Ejemplo:**
```http
GET /api/v1/event-logs/export?start_date=2024-01-01&end_date=2024-01-31
Authorization: Bearer {token}
```

**Respuesta:** Archivo Excel (.xlsx) con todos los eventos en el rango de fechas.

---

## 📁 Estructura del Proyecto

```
products-api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/V1/
│   │   │       ├── Auth/
│   │   │       ├── CurrencyController.php
│   │   │       ├── ProductController.php
│   │   │       ├── ProductPriceController.php
│   │   │       └── EventLogController.php
│   │   ├── Requests/V1/
│   │   ├── Resources/V1/
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Currency.php
│   │   ├── Product.php
│   │   └── ProductPrice.php
│   ├── Services/
│   │   └── EventLogService.php
│   └── Exports/
│       ├── ProductsExport.php
│       ├── ProductPricesExport.php
│       └── EventLogsExport.php
├── database/
│   ├── migrations/
│   └── factories/
├── routes/
│   └── api/v1.php
├── docker/
│   ├── nginx/
│   ├── php/
│   └── postgres/
├── scripts/
│   ├── setup.sh
│   ├── start.sh
│   └── run_tests.sh
└── tests/
    └── Feature/
```

## ⚙️ Configuración

### Variables de Entorno

El archivo `.env` contiene las siguientes configuraciones importantes:

```env
APP_URL=http://localhost:8080
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=products_db
DB_USERNAME=products_user
DB_PASSWORD=products_password
```

### CORS

La API está configurada para aceptar requests desde cualquier origen en desarrollo. En producción, configura `CORS_ALLOWED_ORIGINS` en `.env`.

### Base de Datos

- **Motor:** PostgreSQL 15
- **Puerto:** 5432
- **Base de datos:** `products_db`
- **Usuario:** `products_user`

### Servicios Docker

- **app**: PHP 8.2-FPM con extensiones necesarias
- **nginx**: Servidor web Nginx (puerto 8080)
- **postgres**: Base de datos PostgreSQL 15 (puerto 5432)

### Hot Reload

- **OPcache deshabilitado** para desarrollo
- Los cambios en archivos PHP se reflejan inmediatamente sin reiniciar contenedores

---

## 📝 Notas Importantes

### Respuestas de la API

Todas las respuestas siguen el formato:
```json
{
  "success": boolean,
  "message": string,
  "data": mixed
}
```

### Códigos de Estado HTTP

- `200`: Operación exitosa
- `201`: Recurso creado exitosamente
- `401`: No autenticado (token inválido o faltante)
- `404`: Recurso no encontrado
- `422`: Error de validación
- `409`: Conflicto (ej: intentar eliminar moneda con productos asociados)

### Validación

Todos los mensajes de validación están en **inglés** y siguen el formato estándar de Laravel.

### Event Logs

El sistema registra automáticamente todos los eventos de creación, actualización y eliminación de recursos (Product, Currency, ProductPrice) con:
- Usuario que realizó la acción
- Tipo de evento (POST, PUT, DELETE)
- Recurso afectado
- Payload completo
- IP y User Agent
- Timestamp

---

## 🧪 Testing

Los tests están ubicados en `tests/Feature/` y utilizan `DatabaseTransactions` para mantener la base de datos limpia.

**Ejecutar tests:**
```bash
docker-compose exec app composer test
```

---

## 📚 Documentación Swagger

La documentación interactiva de la API está disponible en:
**http://localhost:8080/api/documentation**

Aquí puedes:
- Ver todos los endpoints disponibles
- Probar los endpoints directamente desde el navegador
- Ver ejemplos de requests y responses
- Autenticarte y usar el token en las peticiones

---

## 🆘 Solución de Problemas

### Error de permisos

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

### Limpiar caché

```bash
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
```

### Reconstruir contenedores

```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

### Ver logs de errores

```bash
docker-compose logs -f app
# o
tail -f storage/logs/laravel.log
```

---

## 📄 Licencia

Este proyecto es parte de un challenge técnico.
