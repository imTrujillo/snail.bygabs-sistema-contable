<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class SaleWidget extends ChartWidget
{
    protected ?string $heading = 'Ingresos por Mes';
    protected static ?int $sort = 4;
    protected  ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Reemplaza con tu lógica real, por ejemplo:
        // $datos = \App\Models\Factura::selectRaw('MONTH(created_at) as mes, SUM(total) as ingreso')
        //     ->whereYear('created_at', now()->year)
        //     ->groupBy('mes')->pluck('ingreso')->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Ingresos ($)',
                    'data'            => [1000, 1500, 1200, 2000, 1800, 2500],
                    'borderColor'     => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth'     => 2,
                    'fill'            => true,
                    'tension'         => 0.4,
                ],
            ],
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
