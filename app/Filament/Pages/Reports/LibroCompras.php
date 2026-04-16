<?php

namespace App\Filament\Pages\Reports;

use App\Models\Expense;
use App\Models\FiscalPeriod;
use App\Models\Purchase;
use App\Models\TaxDocument;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class LibroCompras extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Libro de Compras';

    protected string $view = 'filament.pages.reports.libro-compras';

    public ?int $fiscal_period_id = null;

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('fiscal_period_id')
                ->label('Periodo Tributario')
                ->options(FiscalPeriod::orderBy('start_date')->pluck('name', 'id'))
                ->placeholder('Selecciona un periodo')
                ->live(),
        ]);
    }

    public function getPurchases()
    {
        if (!$this->fiscal_period_id) return collect();

        $period = FiscalPeriod::find($this->fiscal_period_id);

        return Purchase::with(['supplier', 'taxDocument'])
            ->whereBetween('purchase_date', [$period->start_date, $period->end_date])
            ->orderBy('purchase_date')
            ->get();
    }

    public function getTotals()
    {
        $purchases = $this->getPurchases();

        return [
            'exentas'       => $purchases->sum('exempt_amount'),
            'no_gravadas'   => $purchases->sum('non_taxable_amount'),
            'gravadas'      => $purchases->sum('taxable_amount'),
            'credito_fiscal' => $purchases->sum('credit_fiscal'),
            'total'         => $purchases->sum('total_amount'),
        ];
    }

    // Trae el débito fiscal del periodo (desde el libro de ventas)
    public function getDebitoFiscalPeriodo(): float
    {
        if (!$this->fiscal_period_id) return 0;

        $period = FiscalPeriod::find($this->fiscal_period_id);

        return TaxDocument::whereIn('type', ['FCF', 'CCF'])
            ->whereBetween('issue_date', [$period->start_date, $period->end_date])
            ->where('is_voided', false)
            ->sum('iva_amount');
    }

    // En LibroCompras.php
    public function getExpenses()
    {
        if (!$this->fiscal_period_id) return collect();

        $period = FiscalPeriod::find($this->fiscal_period_id);

        // Gastos con CCF (tienen crédito fiscal)
        return Expense::whereBetween('expense_date', [
            $period->start_date,
            $period->end_date
        ])
            ->where('document_type', 'CCF') // solo los que generan crédito
            ->orderBy('expense_date')
            ->get();
    }
}
