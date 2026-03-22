<?php

namespace App\Filament\Pages\Reports;

use App\Models\FiscalPeriod;
use App\Models\JournalEntryLine;
use BackedEnum;
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

    // Suma todos los movimientos de un type de cuenta en el periodo
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

    // Suma por subtype específico
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
        $ingresos          = $this->sumByType('Ingreso');
        $costos            = $this->sumByType('Costo');
        $utilidad_bruta    = $ingresos - $costos;

        $gastos_admin      = $this->sumBySubtype('Administrativo');
        $gastos_venta      = $this->sumBySubtype('Venta');
        $gastos_operativos = $gastos_admin + $gastos_venta;
        $utilidad_operativa = $utilidad_bruta - $gastos_operativos;

        $gastos_financieros = $this->sumBySubtype('Financiero');
        $utilidad_antes_isr = $utilidad_operativa - $gastos_financieros;

        $isr               = $utilidad_antes_isr * 0.25; // 25% ISR
        $utilidad_neta     = $utilidad_antes_isr - $isr;

        return compact(
            'ingresos',
            'costos',
            'utilidad_bruta',
            'gastos_admin',
            'gastos_venta',
            'gastos_operativos',
            'utilidad_operativa',
            'gastos_financieros',
            'utilidad_antes_isr',
            'isr',
            'utilidad_neta'
        );
    }
}
