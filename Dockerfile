FROM php:8.2-fpm

# Argumentos de build
ARG USER_ID=1000
ARG GROUP_ID=1000

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libzip-dev \
    gosu \
    && docker-php-ext-install pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Crear usuario y grupo para desarrollo
RUN groupadd -g ${GROUP_ID} laravel || true
RUN useradd -u ${USER_ID} -g ${GROUP_ID} -m -s /bin/bash laravel || true

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar entrypoint script
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Cambiar al usuario root temporalmente para permisos
USER root

# Copiar archivos de la aplicación
COPY --chown=laravel:laravel . /var/www/html

# Mantener como root para que el entrypoint pueda configurar permisos
# El entrypoint cambiará al usuario laravel antes de ejecutar php-fpm
USER root

# Exponer puerto 9000 para PHP-FPM
EXPOSE 9000

# Entrypoint se ejecutará antes del CMD
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

CMD ["php-fpm"]
