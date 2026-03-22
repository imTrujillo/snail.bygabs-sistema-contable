<?php

namespace App\Filament\Pages\Reports;

use App\Models\FiscalPeriod;
use App\Models\TaxDocument;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class LibroVentas extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Libro de Ventas';

    protected string $view = 'filament.pages.reports.libro-ventas';

    // Campo del filtro
    public ?int $fiscal_period_id = null;

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('fiscal_period_id')
                ->label('Periodo Tributario')
                ->options(FiscalPeriod::orderBy('start_date')->pluck('name', 'id'))
                ->placeholder('Selecciona un periodo')
                ->live(), // recarga al cambiar
        ]);
    }

    // Documentos del periodo seleccionado
    public function getDocuments()
    {
        if (!$this->fiscal_period_id) return collect();

        $period = FiscalPeriod::find($this->fiscal_period_id);

        return TaxDocument::with('client')
            ->whereIn('type', ['FCF', 'CCF'])
            ->whereBetween('issue_date', [$period->start_date, $period->end_date])
            ->where('is_voided', false)
            ->orderBy('issue_date')
            ->get();
    }

    // Totales para el resumen
    public function getTotals()
    {
        $docs = $this->getDocuments();

        return [
            'ventas_exentas'    => $docs->sum('exempt_amount'),
            'ventas_no_grav'    => $docs->sum('non_taxable_amount'),
            'ventas_gravadas'   => $docs->sum('taxable_amount'),
            'debito_fiscal'     => $docs->sum('iva_amount'),
            'total_ventas'      => $docs->sum('total_amount'),
        ];
    }
}
