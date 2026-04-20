<?php

namespace App\Filament\Pages\Reports;

use App\Models\FiscalPeriod;
use App\Models\Purchase;
use App\Models\TaxDocument;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_pdf')
                ->label('Exportar PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn() => (bool) $this->fiscal_period_id)
                ->action(function () {
                    $period    = FiscalPeriod::find($this->fiscal_period_id);
                    $purchases = $this->getPurchases();
                    $totals    = $this->getTotals();
                    $company   = \App\Models\CompanySetting::current();
                    $debitoFiscal  = $this->getDebitoFiscalPeriodo();
                    $creditoFiscal = $totals['credito_fiscal'];
                    $ivaPagar      = $debitoFiscal - $creditoFiscal;

                    $pdf = Pdf::loadView('filament.pages.reports.pdf.libro-compras', compact(
                        'period',
                        'purchases',
                        'totals',
                        'company',
                        'debitoFiscal',
                        'creditoFiscal',
                        'ivaPagar'
                    ))->setPaper('letter', 'landscape');

                    return response()->streamDownload(
                        fn() => print($pdf->output()),
                        "libro-compras-{$period->name}.pdf"
                    );
                }),
        ];
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

    public function getTotals(): array
    {
        $purchases = $this->getPurchases();

        return [
            'exentas'        => $purchases->sum('exempt_amount'),
            'no_gravadas'    => $purchases->sum('non_taxable_amount'),
            'gravadas'       => $purchases->sum('taxable_amount'),
            'credito_fiscal' => $purchases->sum('credit_fiscal'),
            'total'          => $purchases->sum('total_amount'),
        ];
    }

    public function getDebitoFiscalPeriodo(): float
    {
        if (!$this->fiscal_period_id) return 0;

        $period = FiscalPeriod::find($this->fiscal_period_id);

        return TaxDocument::whereIn('type', ['FCF', 'CCF'])
            ->whereBetween('issue_date', [$period->start_date, $period->end_date])
            ->where('is_voided', false)
            ->sum('iva_amount');
    }
}
