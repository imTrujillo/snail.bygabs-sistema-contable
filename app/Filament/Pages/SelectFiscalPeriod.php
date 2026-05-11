<?php

namespace App\Filament\Pages;

use App\Models\FiscalPeriod;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class SelectFiscalPeriod extends Page
{
    protected static ?string $slug = 'select-fiscal-period';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Seleccionar Período Fiscal';

    protected string $view = 'filament.pages.select-fiscal-period';

    public ?int $selectedPeriodId = null;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $this->selectedPeriodId = session('active_fiscal_period_id');
    }

    public function getPeriods(): Collection
    {
        return FiscalPeriod::orderByDesc('start_date')->get();
    }

    public function getActivePeriod(): ?FiscalPeriod
    {
        $id = session('active_fiscal_period_id');

        return $id ? FiscalPeriod::find($id) : null;
    }

    public function selectPeriod(int $periodId): void
    {
        $period = FiscalPeriod::find($periodId);

        if (! $period) {
            Notification::make()
                ->title('Período no encontrado')
                ->danger()
                ->send();

            return;
        }

        if ($period->is_closed) {
            Notification::make()
                ->title('Período cerrado')
                ->body('No puedes trabajar en un período cerrado.')
                ->warning()
                ->send();

            return;
        }

        session(['active_fiscal_period_id' => $period->id]);

        Notification::make()
            ->title("Período activo: {$period->name}")
            ->body("Trabajando en {$period->start_date->locale('es')->isoFormat('MMMM YYYY')}")
            ->success()
            ->send();

        $this->redirect('/admin');
    }
}
