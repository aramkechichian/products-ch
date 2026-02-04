-- Script de inicialización de PostgreSQL
-- Este archivo se ejecuta automáticamente al crear el contenedor por primera vez

-- Crear extensiones útiles si es necesario
-- CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE DATABASE products_db;
GRANT ALL PRIVILEGES ON DATABASE products_db TO products_user;