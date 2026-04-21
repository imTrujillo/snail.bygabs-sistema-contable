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

# Test de que el login responde sin error
php artisan route:list --name=filament.admin.auth.login
echo "--- TEST LOGIN ROUTE ---"
php -r "
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Http\Kernel::class);
\$request = Illuminate\Http\Request::create('/admin/login', 'GET');
\$response = \$kernel->handle(\$request);
echo 'Status: ' . \$response->getStatusCode() . PHP_EOL;
echo substr(\$response->getContent(), 0, 500) . PHP_EOL;
" 2>&1
echo "--- END TEST ---"
php artisan serve --host=0.0.0.0 --port=$PORT
