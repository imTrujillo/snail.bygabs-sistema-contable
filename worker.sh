#!/bin/bash

chmod -R 777 /app/storage /app/bootstrap/cache

php artisan filament:upgrade

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

php artisan config:cache
php artisan route:cache

php artisan storage:link --quiet || true

php artisan queue:work --tries=3 --timeout=90 &

php artisan serve --host=0.0.0.0 --port=$PORT
