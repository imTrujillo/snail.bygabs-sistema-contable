#!/bin/bash

# Permisos de storage
chmod -R 777 /app/storage /app/bootstrap/cache

# Limpiar caches y regenerar (por si el predeploy no persistió)
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link --quiet || true

# Queue worker en background
php artisan queue:work --tries=3 --timeout=90 &

# Servidor
php artisan serve --host=0.0.0.0 --port=$PORT
