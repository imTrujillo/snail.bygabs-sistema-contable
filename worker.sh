#!/bin/bash

chmod -R 777 /app/storage /app/bootstrap/cache

# Republicar assets de Filament
php artisan filament:upgrade

# Limpiar TODO el cache primero
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Regenerar caches limpios
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --quiet || true

# Queue worker en background
php artisan queue:work --tries=3 --timeout=90 &

# Servidor
php artisan serve --host=0.0.0.0 --port=$PORT
