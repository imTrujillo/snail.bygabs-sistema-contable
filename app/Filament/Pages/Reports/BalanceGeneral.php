<?php

namespace App\Filament\Pages\Reports;

use App\Models\Account;
use App\Models\CompanySetting;
use App\Models\FiscalPeriod;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Collection;
use UnitEnum;

class BalanceGeneral extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = 'Reportes';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationLabel = 'Balance General';

    protected string $view = 'filament.pages.reports.balance-general';

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
                    $data = $this->getBalanceData();
                    $company = CompanySetting::current();

                    $pdf = Pdf::loadView('filament.pages.reports.pdf.balance-general', compact('period', 'data', 'company'))
                        ->setPaper('letter', 'portrait');

                    return response()->streamDownload(
                        fn () => print ($pdf->output()),
                        "balance-general-{$period->name}.pdf"
                    );
                }),
        ];
    }

    public function getAccountsByType(string $type): Collection
    {
        if (! $this->fiscal_period_id) {
            return collect();
        }

        return Account::where('type', $type)
            ->where('is_group', false)
            ->with([
                'journalLines' => fn ($q) => $q->whereHas(
                    'journalEntry',
                    fn ($q) => $q->where('fiscal_period_id', $this->fiscal_period_id)
                ),
            ])
            ->get()
            ->map(function ($account) {
                $debit = $account->journalLines->sum('debit');
                $credit = $account->journalLines->sum('credit');

                // ✅ fix: comparación con mayúscula
                $balance = in_array($account->type, ['Activo', 'Costo', 'Gasto'])
                    ? $debit - $credit
                    : $credit - $debit;

                return [
                    'code' => $account->code,
                    'name' => $account->name,
                    'subtype' => $account->subtype,
                    'balance' => $balance,
                ];
            })
            ->filter(fn ($a) => $a['balance'] != 0)
            ->values();
    }

    public function getBalanceData(): array
    {
        $activos = $this->getAccountsByType('Activo');
        $pasivos = $this->getAccountsByType('Pasivo');
        $patrimonio = $this->getAccountsByType('Patrimonio');

        // Cuentas nominales del período (sin asiento de cierre): ingresan en el lado derecho para cuadrar con activos.
        $ingresos = $this->getAccountsByType('Ingreso');
        $costos = $this->getAccountsByType('Costo');
        $gastos = $this->getAccountsByType('Gasto');

        $totalIngresosNominal = $ingresos->sum('balance');
        $totalCostosNominal = $costos->sum('balance');
        $totalGastosNominal = $gastos->sum('balance');
        $resultadoPeriodo = $totalIngresosNominal - $totalCostosNominal - $totalGastosNominal;

        $activos_corrientes = $activos->where('subtype', 'Corriente')->values();
        $activos_no_corrientes = $activos->where('subtype', 'No Corriente')->values();
        $pasivos_corrientes = $pasivos->where('subtype', 'Corriente')->values();
        $pasivos_no_corrientes = $pasivos->where('subtype', 'No Corriente')->values();

        $totalActivos = $activos_corrientes->sum('balance') + $activos_no_corrientes->sum('balance');
        $totalPasivos = $pasivos_corrientes->sum('balance') + $pasivos_no_corrientes->sum('balance');
        $totalPatrimonio = $patrimonio->sum('balance');

        return [
            'activos_corrientes' => $activos_corrientes,
            'activos_no_corrientes' => $activos_no_corrientes,
            'pasivos_corrientes' => $pasivos_corrientes,
            'pasivos_no_corrientes' => $pasivos_no_corrientes,
            'patrimonio' => $patrimonio,
            'ingresos' => $ingresos,
            'costos' => $costos,
            'gastos' => $gastos,
            'total_activos' => $totalActivos,
            'total_pasivos' => $totalPasivos,
            'total_patrimonio' => $totalPatrimonio,
            'total_ingresos_nominal' => $totalIngresosNominal,
            'total_costos_nominal' => $totalCostosNominal,
            'total_gastos_nominal' => $totalGastosNominal,
            'resultado_periodo' => $resultadoPeriodo,
            /** Pasivos + patrimonio contable + resultado del período (cuentas Ingreso/Gasto/Costo). */
            'total_derecha' => $totalPasivos + $totalPatrimonio + $resultadoPeriodo,
        ];
    }
}
