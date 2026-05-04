<?php

namespace App\Filament\Widgets;

use App\Models\TaxDocument;
use Filament\Widgets\ChartWidget;

class InvoiceWidget extends ChartWidget
{
    protected ?string $heading = 'Documentos Fiscales por Mes';

    protected static ?int $sort = 3;

    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $meses = collect(range(1, 12));

        $documentos = TaxDocument::query()
            ->selectRaw('MONTH(issue_date) as mes, COUNT(*) as total')
            ->where('is_voided', false)
            ->whereYear('issue_date', now()->year)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $data = $meses->map(fn ($m) => $documentos->get($m, 0))->toArray();
        $labels = $meses->map(fn ($m) => now()->month($m)->locale('es')->isoFormat('MMM'))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Documentos fiscales',
                    'data' => $data,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
