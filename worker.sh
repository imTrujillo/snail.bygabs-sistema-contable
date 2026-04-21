#!/bin/bash

chmod -R 777 /app/storage /app/bootstrap/cache

php artisan filament:upgrade

php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan config:cache

php artisan session:table 2>/dev/null || true
php artisan migrate --force

php artisan storage:link --quiet || true

php artisan queue:work --tries=3 --timeout=90 &


echo "=== DIAGNOSTICO 403 ==="
php -r "
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$kernel = \$app->make(Illuminate\Contracts\Http\Kernel::class);

// Test /admin sin auth
\$r1 = Illuminate\Http\Request::create('/admin', 'GET');
\$res1 = \$kernel->handle(\$r1);
echo 'GET /admin (sin auth): ' . \$res1->getStatusCode() . PHP_EOL;

// Test /admin/select-fiscal-period sin auth
\$r2 = Illuminate\Http\Request::create('/admin/select-fiscal-period', 'GET');
\$res2 = \$kernel->handle(\$r2);
echo 'GET /admin/select-fiscal-period (sin auth): ' . \$res2->getStatusCode() . PHP_EOL;

// Test /admin/login
\$r3 = Illuminate\Http\Request::create('/admin/login', 'GET');
\$res3 = \$kernel->handle(\$r3);
echo 'GET /admin/login: ' . \$res3->getStatusCode() . PHP_EOL;
" 2>&1
echo "=== FIN DIAGNOSTICO ==="

php artisan serve --host=0.0.0.0 --port=$PORT
