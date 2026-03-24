<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class AppointmentWidget extends ChartWidget
{
    protected  ?string $heading = 'Citas por Tipo';
    protected static ?int $sort = 3;
    protected  ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Reemplaza con tu lógica real, por ejemplo:
        // $consultas     = \App\Models\Cita::where('tipo', 'consulta')->count();
        // $mantenimiento = \App\Models\Cita::where('tipo', 'mantenimiento')->count();
        // $emergencia    = \App\Models\Cita::where('tipo', 'emergencia')->count();

        return [
            'datasets' => [
                [
                    'data'            => [20, 15, 10],
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Consulta', 'Mantenimiento', 'Emergencia'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
