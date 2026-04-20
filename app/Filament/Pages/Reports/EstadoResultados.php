<?php

namespace App\Filament\Pages\Reports;

use App\Models\FiscalPeriod;
use App\Models\JournalEntryLine;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use UnitEnum;

class EstadoResultados extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Estado de Resultados';
    protected string $view = 'filament.pages.reports.estado-resultados';

    public ?int $fiscal_period_id = null;

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Select::make('fiscal_period_id')
                ->label('Periodo')
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
                    $period  = FiscalPeriod::find($this->fiscal_period_id);
                    $r       = $this->getResultados();
                    $company = \App\Models\CompanySetting::current();

                    $pdf = Pdf::loadView('filament.pages.reports.pdf.estado-resultados', compact('period', 'r', 'company'))
                        ->setPaper('letter', 'portrait');

                    return response()->streamDownload(
                        fn() => print($pdf->output()),
                        "estado-resultados-{$period->name}.pdf"
                    );
                }),
        ];
    }

    private function sumByType(string $type): float
    {
        if (!$this->fiscal_period_id) return 0;

        return JournalEntryLine::whereHas('journalEntry', fn($q) =>
        $q->where('fiscal_period_id', $this->fiscal_period_id))
            ->whereHas('account', fn($q) =>
            $q->where('type', $type)->where('is_group', false))
            ->selectRaw('SUM(credit) - SUM(debit) as total')
            ->value('total') ?? 0;
    }

    private function sumBySubtype(string $subtype): float
    {
        if (!$this->fiscal_period_id) return 0;

        return JournalEntryLine::whereHas('journalEntry', fn($q) =>
        $q->where('fiscal_period_id', $this->fiscal_period_id))
            ->whereHas('account', fn($q) =>
            $q->where('subtype', $subtype)->where('is_group', false))
            ->selectRaw('SUM(debit) - SUM(credit) as total')
            ->value('total') ?? 0;
    }

    public function getResultados(): array
    {
        $ingresos           = $this->sumByType('Ingreso');
        $costos             = $this->sumByType('Costo');
        $utilidad_bruta     = $ingresos - $costos;
        $gastos_admin       = $this->sumBySubtype('Administrativo');
        $gastos_venta       = $this->sumBySubtype('Venta');
        $utilidad_operativa = $utilidad_bruta - $gastos_admin - $gastos_venta;
        $gastos_financieros = $this->sumBySubtype('Financiero');
        $utilidad_antes_isr = $utilidad_operativa - $gastos_financieros;

        // ISR El Salvador: si ingresos > $150,000 → 30% flat
        // Si no, tabla progresiva
        if ($utilidad_antes_isr <= 0) {
            $isr = 0;
        } elseif ($ingresos > 150000) {
            $isr = round($utilidad_antes_isr * 0.30, 2);
        } else {
            $isr = match (true) {
                $utilidad_antes_isr <= 4064.00   => 0,
                $utilidad_antes_isr <= 9142.86   => round(($utilidad_antes_isr - 4064.00) * 0.10, 2),
                $utilidad_antes_isr <= 22857.14  => round(507.86 + ($utilidad_antes_isr - 9142.86) * 0.20, 2),
                default                          => round(3230.43 + ($utilidad_antes_isr - 22857.14) * 0.30, 2),
            };
        }

        $utilidad_neta = $utilidad_antes_isr - $isr;

        return compact(
            'ingresos',
            'costos',
            'utilidad_bruta',
            'gastos_admin',
            'gastos_venta',
            'utilidad_operativa',
            'gastos_financieros',
            'utilidad_antes_isr',
            'isr',
            'utilidad_neta'
        );
    }
}
