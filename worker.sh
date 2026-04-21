#!/bin/bash

set -e  # Para que falle rápido y veas el error real

chmod -R 777 /app/storage /app/bootstrap/cache

php artisan filament:upgrade

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache

# Ver rutas ANTES de servir (output va a logs)
echo "=== RUTAS ADMIN ==="
php artisan route:list 2>&1 | grep -i admin || echo "NO SE ENCONTRARON RUTAS ADMIN"
echo "==================="

php artisan storage:link --quiet || true

php artisan queue:work --tries=3 --timeout=90 &

php artisan serve --host=0.0.0.0 --port=$PORT
