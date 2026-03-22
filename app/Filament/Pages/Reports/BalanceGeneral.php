<?php

namespace App\Filament\Pages\Reports;

use App\Models\Account;
use App\Models\FiscalPeriod;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
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

    // Obtiene cuentas hoja de un type con su saldo en el periodo
    public function getAccountsByType(string $type): \Illuminate\Support\Collection
    {
        if (!$this->fiscal_period_id) return collect();

        return Account::where('type', $type)
            ->where('is_group', false)
            ->with([
                'journalLines' => fn($q) =>
                $q->whereHas(
                    'journalEntry',
                    fn($q) =>
                    $q->where('fiscal_period_id', $this->fiscal_period_id)
                )
            ])
            ->get()
            ->map(function ($account) {
                $debit  = $account->journalLines->sum('debit');
                $credit = $account->journalLines->sum('credit');
                // Activos: saldo = débito - crédito
                // Pasivos/Patrimonio: saldo = crédito - débito
                $balance = in_array($account->type, ['activo'])
                    ? $debit - $credit
                    : $credit - $debit;

                return [
                    'code'    => $account->code,
                    'name'    => $account->name,
                    'subtype' => $account->subtype,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($a) => $a['balance'] != 0); // solo cuentas con movimiento
    }

    public function getBalanceData(): array
    {
        $activos_corrientes    = $this->getAccountsByType('Activo')->where('subtype', 'Corriente');
        $activos_no_corrientes = $this->getAccountsByType('Activo')->where('subtype', 'No Corriente');
        $pasivos_corrientes    = $this->getAccountsByType('Pasivo')->where('subtype', 'Corriente');
        $pasivos_no_corrientes = $this->getAccountsByType('Pasivo')->where('subtype', 'No Corriente');
        $patrimonio            = $this->getAccountsByType('Patrimonio');

        return [
            'activos_corrientes'    => $activos_corrientes,
            'activos_no_corrientes' => $activos_no_corrientes,
            'pasivos_corrientes'    => $pasivos_corrientes,
            'pasivos_no_corrientes' => $pasivos_no_corrientes,
            'patrimonio'            => $patrimonio,
            'total_activos'         => $activos_corrientes->sum('balance') + $activos_no_corrientes->sum('balance'),
            'total_pasivos'         => $pasivos_corrientes->sum('balance') + $pasivos_no_corrientes->sum('balance'),
            'total_patrimonio'      => $patrimonio->sum('balance'),
        ];
    }
}
