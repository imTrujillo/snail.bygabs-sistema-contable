<?php

namespace App\Filament\Widgets;

use App\Models\TaxDocument;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class SaleWidget extends ChartWidget
{
    protected ?string $heading = 'Ingresos por Mes';

    protected static ?int $sort = 4;

    protected ?string $maxHeight = '300px';

    private function issuedSaleDocs(): Builder
    {
        return TaxDocument::query()
            ->where('reference_type', 'sale')
            ->where('is_voided', false)
            ->whereHas('sale', function (Builder $sq) {
                if (Schema::hasColumn('sales', 'status')) {
                    $sq->where('status', '!=', 'anulada');
                }
            });
    }

    protected function getData(): array
    {
        $meses = collect(range(1, 12));

        $ventasDocs = $this->issuedSaleDocs()
            ->selectRaw('MONTH(issue_date) as mes, SUM(total_amount) as total')
            ->whereYear('issue_date', now()->year)
            ->groupBy('mes')
            ->pluck('total', 'mes');

        $data = $meses->map(fn ($m) => round((float) $ventasDocs->get($m, 0), 2))->toArray();
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
