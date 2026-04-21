#!/bin/bash

chmod -R 777 /app/storage /app/bootstrap/cache

php artisan filament:upgrade

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache    # ← Agregar esto, Filament v4 SÍ es compatible
php artisan view:cache     # ← Agregar esto también

php artisan storage:link --quiet || true

php artisan queue:work --tries=3 --timeout=90 &

php artisan route:list | grep admin
php artisan serve --host=0.0.0.0 --port=$PORT
