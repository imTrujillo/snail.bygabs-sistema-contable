<?php

namespace App\Http\Middleware;

use App\Models\FiscalPeriod;
use Closure;
use Illuminate\Http\Request;

class EnsureActiveFiscalPeriod
{
    public function handle(Request $request, Closure $next)
    {
        $excluded = [
            'admin/select-fiscal-period',  // slug correcto
            'admin/login',
            'admin/logout',
            'livewire/update',
        ];

        foreach ($excluded as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        if (!session('active_fiscal_period_id')) {
            return redirect('/admin/select-fiscal-period');
        }

        $period = FiscalPeriod::find(session('active_fiscal_period_id'));
        if (!$period || $period->is_closed) {
            session()->forget('active_fiscal_period_id');
            return redirect('/admin/select-fiscal-period')
                ->with('warning', 'El período seleccionado fue cerrado. Selecciona uno nuevo.');
        }

        return $next($request);
    }
}
