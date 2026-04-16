<?php

namespace App\Filament\Pages;

use App\Models\FiscalPeriod;
use Filament\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

class SelectFiscalPeriod extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $slug = 'select-fiscal-period';
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $title = 'Seleccionar Período';

    protected string $view = 'filament.pages.select-fiscal-period';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->statePath('data')
            ->schema([
                Select::make('fiscal_period_id')
                    ->label('Período de trabajo')
                    ->options(
                        FiscalPeriod::where('is_closed', false)
                            ->orderByDesc('start_date')
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable()
                    ->placeholder('Selecciona el mes a trabajar'),
            ]);
    }

    public function select(): void
    {
        $data = $this->form->getState();
        $period = FiscalPeriod::find($data['fiscal_period_id']);

        session(['active_fiscal_period_id' => $data['fiscal_period_id']]);

        Notification::make()
            ->title("Período activo: {$period->name}")
            ->success()
            ->send();

        $this->redirect('/admin');
    }
}
