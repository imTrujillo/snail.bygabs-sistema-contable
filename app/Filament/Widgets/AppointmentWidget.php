<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;

class AppointmentWidget extends ChartWidget
{
    protected ?string $heading = 'Citas por Estado';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $estados = ['Pendiente', 'Completada', 'Cancelada'];

        $conteos = collect($estados)->map(
            fn($estado) => Appointment::where('status', $estado)->count()
        )->toArray();

        return [
            'datasets' => [
                [
                    'data'            => $conteos,
                    'backgroundColor' => [
                        'rgba(245, 158, 11, 0.8)',  // Pendiente  → amarillo
                        'rgba(16, 185, 129, 0.8)',  // Completada → verde
                        'rgba(239, 68, 68, 0.8)',   // Cancelada  → rojo
                    ],
                    'borderColor' => [
                        'rgb(245, 158, 11)',
                        'rgb(16, 185, 129)',
                        'rgb(239, 68, 68)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $estados,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
