<?php

namespace App\Filament\Pages\Reports;

use App\Models\CompanySetting;
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
                ->visible(fn () => (bool) $this->fiscal_period_id)
                ->action(function () {
                    $period = FiscalPeriod::find($this->fiscal_period_id);
                    $r = $this->getResultados();
                    $company = CompanySetting::current();

                    $pdf = Pdf::loadView('filament.pages.reports.pdf.estado-resultados', compact('period', 'r', 'company'))
                        ->setPaper('letter', 'portrait');

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        "estado-resultados-{$period->name}.pdf"
                    );
                }),
        ];
    }

    private function sumByType(string $type): float
    {
        if (! $this->fiscal_period_id) {
            return 0;
        }

        $query = JournalEntryLine::whereHas('journalEntry', fn ($q) => $q->where('fiscal_period_id', $this->fiscal_period_id))
            ->whereHas('account', fn ($q) => $q->where('type', $type)->where('is_group', false));

        if ($type === 'Ingreso') {
            return (float) ($query->clone()->selectRaw('SUM(credit) - SUM(debit) as total')->value('total') ?? 0);
        }

        return (float) ($query->clone()->selectRaw('SUM(debit) - SUM(credit) as total')->value('total') ?? 0);
    }

    /**
     * Suma líneas del mayor por subtipo de cuenta nominal (p. ej. Administrativo, Venta).
     * Restringe por tipo de cuenta (por defecto Gasto) para no mezclar p. ej. "No Operativo"
     * en ingresos (4200) con gastos no operativos (6400).
     * Comparación de subtipo sin distinguir mayúsculas (corrige administrativo vs Administrativo).
     */
    private function sumBySubtype(string $subtype, string $accountType = 'Gasto'): float
    {
        if (! $this->fiscal_period_id) {
            return 0;
        }

        $key = strtolower(trim($subtype));

        return (float) (JournalEntryLine::whereHas('journalEntry', fn ($q) => $q->where('fiscal_period_id', $this->fiscal_period_id))
            ->whereHas('account', function ($q) use ($key, $accountType) {
                $q->where('type', $accountType)
                    ->where('is_group', false)
                    ->whereRaw('LOWER(TRIM(subtype)) = ?', [$key]);
            })
            ->selectRaw('SUM(debit) - SUM(credit) as total')
            ->value('total') ?? 0);
    }

    public function getResultados(): array
    {
        $ingresos = $this->sumByType('Ingreso');
        $costos = $this->sumByType('Costo');
        $utilidad_bruta = $ingresos - $costos;
        $gastos_admin = $this->sumBySubtype('Administrativo');
        $gastos_venta = $this->sumBySubtype('Venta');
        $utilidad_operativa = $utilidad_bruta - $gastos_admin - $gastos_venta;
        $gastos_financieros = $this->sumBySubtype('Financiero');
        $gastos_no_operativos = $this->sumBySubtype('No Operativo');
        $utilidad_antes_isr = $utilidad_operativa - $gastos_financieros - $gastos_no_operativos;

        // ISR El Salvador: si ingresos > $150,000 → 30% flat
        // Si no, tabla progresiva
        if ($utilidad_antes_isr <= 0) {
            $isr = 0;
        } elseif ($ingresos > 150000) {
            $isr = round($utilidad_antes_isr * 0.30, 2);
        } else {
            $isr = match (true) {
                $utilidad_antes_isr <= 4064.00 => 0,
                $utilidad_antes_isr <= 9142.86 => round(($utilidad_antes_isr - 4064.00) * 0.10, 2),
                $utilidad_antes_isr <= 22857.14 => round(507.86 + ($utilidad_antes_isr - 9142.86) * 0.20, 2),
                default => round(3230.43 + ($utilidad_antes_isr - 22857.14) * 0.30, 2),
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
            'gastos_no_operativos',
            'utilidad_antes_isr',
            'isr',
            'utilidad_neta'
        );
    }
}
