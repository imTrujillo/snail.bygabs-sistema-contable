<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Reemplaza estos valores con tus consultas reales a la base de datos
        // Ejemplo: $clientes = \App\Models\Cliente::count();

        $clientes   = 120;
        $facturas   = 340;
        $ingresos   = 8500;
        $citas      = 45;

        return [
            Stat::make('Clientes', $clientes)
                ->description('Total de clientes registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([65, 78, 90, 100, 110, 120]),

            Stat::make('Facturas', $facturas)
                ->description('Total de facturas emitidas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart([120, 180, 200, 260, 300, 340]),

            Stat::make('Ingresos', '$' . number_format($ingresos, 2))
                ->description('Ingresos totales del período')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning')
                ->chart([1000, 1500, 1200, 2000, 1800, 2500]),

            Stat::make('Citas', $citas)
                ->description('Citas agendadas')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info')
                ->chart([5, 12, 20, 30, 38, 45]),
        ];
    }
}
