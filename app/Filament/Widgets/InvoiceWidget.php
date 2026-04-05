<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class InvoiceWidget extends ChartWidget
{
    protected  ?string $heading = 'Facturas por Mes';
    protected static ?int $sort = 3;
    protected  ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Reemplaza con tu lógica real, por ejemplo:
        // $datos = \App\Models\Factura::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
        //     ->whereYear('created_at', now()->year)
        //     ->groupBy('mes')->pluck('total')->toArray();

        return [
            'datasets' => [
                [
                    'label'           => 'Facturas por mes',
                    'data'            => [12, 19, 10, 25, 30, 40],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor'     => 'rgb(59, 130, 246)',
                    'borderWidth'     => 2,
                ],
            ],
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
