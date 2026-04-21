#!/bin/bash

chmod -R 777 /app/storage /app/bootstrap/cache

# Republicar assets de Filament (se pierden en el COPY final del build)
php artisan filament:upgrade

# Limpiar y regenerar caches en el contenedor final
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:clear
php artisan view:cache
php artisan storage:link --quiet || true

# Queue worker en background
php artisan queue:work --tries=3 --timeout=90 &

# Servidor
php artisan serve --host=0.0.0.0 --port=$PORT
