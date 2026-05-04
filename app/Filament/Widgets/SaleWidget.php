<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Schema;

class SaleWidget extends ChartWidget
{
    protected ?string $heading = 'Ingresos por Mes';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $meses = collect(range(1, 12));

        $ventas = Sale::query()
            ->when(
                Schema::hasColumn('sales', 'status'),
                fn ($q) => $q->where('status', '!=', 'anulada')
            )
            ->selectRaw('MONTH(created_at) as mes, SUM(total) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $data = $meses->map(fn ($m) => round($ventas->get($m, 0), 2))->toArray();
        $labels = $meses->map(fn ($m) => now()->month($m)->locale('es')->isoFormat('MMM'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos ($)',
                    'data' => $data,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
