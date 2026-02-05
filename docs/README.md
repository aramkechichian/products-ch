# Colecciones Postman - Products API

Esta carpeta contiene las colecciones de Postman para probar todos los endpoints de la API.

## Archivos Disponibles

- **Products-API-Local.postman_collection.json** - Para entorno local (http://localhost:8080)

## Cómo Importar en Postman

1. Abre Postman
2. Haz clic en **Import** (botón en la esquina superior izquierda)
3. Selecciona el archivo JSON que quieres importar
4. La colección se agregará a tu workspace

## Configuración

### Variables de la Colección

Cada colección tiene dos variables predefinidas:

- **`base_url`**: URL base de la API
  - Local: `http://localhost:8080`

- **`token`**: Token de autenticación (se guarda automáticamente después de hacer login)

### Autenticación Automática

El endpoint **Login** tiene un script que guarda automáticamente el token en la variable `token` después de un login exitoso. Esto significa que:

1. Primero ejecuta **Login** o **Register**
2. El token se guardará automáticamente
3. Todos los demás endpoints usarán ese token automáticamente

## Endpoints Incluidos

### Authentication
- ✅ Register
- ✅ Login (guarda token automáticamente)
- ✅ Get Me
- ✅ Logout
- ✅ Logout All

### Products
- ✅ Get All Products
- ✅ Create Product
- ✅ Get Product by ID
- ✅ Update Product
- ✅ Delete Product
- ✅ Search Products (con filtros avanzados)
- ✅ Export Products to Excel

### Product Prices
- ✅ Get Product Prices
- ✅ Create Product Price
- ✅ Export Product Prices to Excel

### Currencies
- ✅ Get All Currencies
- ✅ Create Currency
- ✅ Get Currency by ID
- ✅ Update Currency
- ✅ Delete Currency

### Event Logs
- ✅ Get All Event Logs
- ✅ Get Event Log by ID
- ✅ Export Event Logs to Excel

## Uso Rápido

1. **Importa la colección** que necesites (Local o Laravel Cloud)
2. **Ejecuta "Register" o "Login"** para obtener un token
3. **El token se guarda automáticamente** en la variable `token`
4. **Prueba cualquier otro endpoint** - el token se incluirá automáticamente

## Notas

- Los endpoints de **exportación a Excel** descargarán un archivo .xlsx
- Los endpoints protegidos requieren autenticación (token Bearer)
- Los parámetros de URL (como `:id`) pueden editarse directamente en Postman
- Los query parameters están deshabilitados por defecto - habilítalos según necesites

## Solución de Problemas

### El token no se guarda automáticamente

Si el token no se guarda después del login:
1. Ve a la pestaña **Tests** del endpoint Login
2. Verifica que el script esté presente
3. O guarda el token manualmente desde la respuesta

### Error 401 Unauthorized

- Verifica que hayas ejecutado Login/Register primero
- Verifica que la variable `token` tenga un valor
- El token puede haber expirado - ejecuta Login nuevamente

### Error 404 Not Found

- Verifica que la variable `base_url` esté correcta
- Verifica que el servidor esté corriendo (local) o que Laravel Cloud esté activo
