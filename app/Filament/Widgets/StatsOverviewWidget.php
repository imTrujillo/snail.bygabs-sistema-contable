<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\TaxDocument;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Schema;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $clientes = Customer::count();

        $facturas = TaxDocument::count();

        $activeSales = Sale::query()->when(
            Schema::hasColumn('sales', 'status'),
            fn ($q) => $q->where('status', '!=', 'anulada')
        );

        $hoy = (float) $activeSales
            ->whereDate('created_at', today())
            ->sum('total');

        $ayer = (float) $activeSales
            ->whereDate('created_at', today()->subDay())
            ->sum('total');

        $ultimos7 = collect(range(6, 0))->map(
            fn (int $d) => (float) $activeSales
                ->whereDate('created_at', today()->subDays($d))
                ->sum('total')
        )->toArray();

        $delta = $ayer > 0 ? round((($hoy - $ayer) / $ayer) * 100, 1) : null;

        $ingresos = Sale::query()
            ->when(
                Schema::hasColumn('sales', 'status'),
                fn ($q) => $q->where('status', '!=', 'anulada')
            )
            ->sum('total');

        $citas = Appointment::count();

        // Sparklines: últimas 6 semanas
        $ventasSemanas = collect(range(5, 0))->map(
            fn ($i) => Sale::query()
                ->when(
                    Schema::hasColumn('sales', 'status'),
                    fn ($q) => $q->where('status', '!=', 'anulada')
                )
                ->whereBetween('created_at', [
                    now()->subWeeks($i + 1)->startOfWeek(),
                    now()->subWeeks($i)->endOfWeek(),
                ])->sum('total')
        )->toArray();

        $facturasSemanas = collect(range(5, 0))->map(
            fn ($i) => TaxDocument::whereBetween('created_at', [
                now()->subWeeks($i + 1)->startOfWeek(),
                now()->subWeeks($i)->endOfWeek(),
            ])->count()
        )->toArray();

        $clientesSemanas = collect(range(5, 0))->map(
            fn ($i) => Customer::whereBetween('created_at', [
                now()->subWeeks($i + 1)->startOfWeek(),
                now()->subWeeks($i)->endOfWeek(),
            ])->count()
        )->toArray();

        $citasSemanas = collect(range(5, 0))->map(
            fn ($i) => Appointment::whereBetween('created_at', [
                now()->subWeeks($i + 1)->startOfWeek(),
                now()->subWeeks($i)->endOfWeek(),
            ])->count()
        )->toArray();

        return [

            Stat::make('Ventas del día', '$'.number_format($hoy, 2))
                ->description(
                    $delta === null
                        ? 'Comparado con ayer: sin ventas ayer'
                        : ($delta >= 0 ? '↑ ' : '↓ ').abs($delta).'% vs. ayer ($'.number_format($ayer, 2).')'
                )
                ->descriptionIcon('heroicon-m-calendar')
                ->color($hoy >= $ayer ? 'success' : 'warning')
                ->chart($ultimos7),

            Stat::make('Facturas', $facturas)
                ->description('Total de facturas emitidas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart($facturasSemanas),

            Stat::make('Ingresos', '$'.number_format($ingresos, 2))
                ->description('Ingresos totales')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning')
                ->chart($ventasSemanas),

            Stat::make('Citas', $citas)
                ->description('Citas agendadas')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart($citasSemanas),
        ];
    }
}
