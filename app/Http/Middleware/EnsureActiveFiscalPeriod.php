<?php

namespace App\Http\Middleware;

use App\Models\FiscalPeriod;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveFiscalPeriod
{
    public function handle(Request $request, Closure $next)
    {
        // Rutas que no necesitan período
        $excluded = [
            'admin/fiscal-periods/select',
            'admin/login',
            'livewire/update',
        ];

        foreach ($excluded as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        // Si no hay período en sesión, redirigir a selección
        if (!session('active_fiscal_period_id')) {
            return redirect('/admin/fiscal-periods/select');
        }

        // Verificar que el período sigue abierto
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));
        if (!$period || $period->is_closed) {
            session()->forget('active_fiscal_period_id');
            return redirect('/admin/fiscal-periods/select')
                ->with('warning', 'El período seleccionado fue cerrado. Selecciona uno nuevo.');
        }

        return $next($request);
    }
}
