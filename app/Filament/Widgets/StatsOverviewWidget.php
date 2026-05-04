<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\TaxDocument;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    private function issuedSaleDocuments(): Builder
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

    /** Ventas con documento fiscal usan fecha de emisión; sin documento, cae en created_at de la venta. */
    private function sumSalesForDate(\Carbon\CarbonInterface $date): float
    {
        $fromDocs = (float) $this->issuedSaleDocuments()
            ->whereDate('issue_date', $date)
            ->sum('total_amount');

        $fromOrphans = (float) Sale::query()
            ->when(
                Schema::hasColumn('sales', 'status'),
                fn ($q) => $q->where('status', '!=', 'anulada')
            )
            ->whereNull('tax_document_id')
            ->whereDate('created_at', $date)
            ->sum('total');

        return $fromDocs + $fromOrphans;
    }

    protected function getStats(): array
    {
        $clientes = Customer::count();

        $facturas = TaxDocument::query()->where('is_voided', false)->count();

        $hoy = $this->sumSalesForDate(today());
        $ayer = $this->sumSalesForDate(today()->subDay());

        $ultimos7 = collect(range(6, 0))->map(
            fn (int $d) => $this->sumSalesForDate(today()->subDays($d))
        )->toArray();

        $delta = $ayer > 0 ? round((($hoy - $ayer) / $ayer) * 100, 1) : null;

        $ingresos = (float) $this->issuedSaleDocuments()->sum('total_amount')
            + (float) Sale::query()
                ->when(
                    Schema::hasColumn('sales', 'status'),
                    fn ($q) => $q->where('status', '!=', 'anulada')
                )
                ->whereNull('tax_document_id')
                ->sum('total');

        $citas = Appointment::count();

        $ventasSemanas = collect(range(5, 0))->map(
            fn ($i) => (float) $this->issuedSaleDocuments()
                ->whereBetween('issue_date', [
                    now()->subWeeks($i + 1)->startOfWeek(),
                    now()->subWeeks($i)->endOfWeek(),
                ])->sum('total_amount')
        )->toArray();

        $facturasSemanas = collect(range(5, 0))->map(
            fn ($i) => TaxDocument::query()
                ->where('is_voided', false)
                ->whereBetween('issue_date', [
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
                ->description('Documentos fiscales activos')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('success')
                ->chart($facturasSemanas),

            Stat::make('Ingresos', '$'.number_format($ingresos, 2))
                ->description('Total facturado (documentos de venta)')
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
