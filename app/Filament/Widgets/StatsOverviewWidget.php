<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\TaxDocument;
use App\Models\Appointment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $clientes = Customer::count();

        $facturas = TaxDocument::count();

        $ingresos = Sale::sum('total');

        $citas = Appointment::count();

        // Sparklines: últimas 6 semanas
        $ventasSemanas = collect(range(5, 0))->map(
            fn($i) =>
            Sale::whereBetween('created_at', [
                now()->subWeeks($i + 1)->startOfWeek(),
                now()->subWeeks($i)->endOfWeek(),
            ])->sum('total')
        )->toArray();

        $facturasSemanas = collect(range(5, 0))->map(
            fn($i) =>
            TaxDocument::whereBetween('created_at', [
                now()->subWeeks($i + 1)->startOfWeek(),
                now()->subWeeks($i)->endOfWeek(),
            ])->count()
        )->toArray();

        $clientesSemanas = collect(range(5, 0))->map(
            fn($i) =>
            Customer::whereBetween('created_at', [
                now()->subWeeks($i + 1)->startOfWeek(),
                now()->subWeeks($i)->endOfWeek(),
            ])->count()
        )->toArray();

        $citasSemanas = collect(range(5, 0))->map(
            fn($i) =>
            Appointment::whereBetween('created_at', [
                now()->subWeeks($i + 1)->startOfWeek(),
                now()->subWeeks($i)->endOfWeek(),
            ])->count()
        )->toArray();

        return [
            Stat::make('Clientes', $clientes)
                ->description('Total de clientes registrados')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart($clientesSemanas),

            Stat::make('Facturas', $facturas)
                ->description('Total de facturas emitidas')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart($facturasSemanas),

            Stat::make('Ingresos', '$' . number_format($ingresos, 2))
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
