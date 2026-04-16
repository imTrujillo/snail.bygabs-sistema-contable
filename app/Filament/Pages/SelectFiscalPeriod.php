<?php

namespace App\Filament\Pages;

use App\Models\FiscalPeriod;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;

class SelectFiscalPeriod extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.select-fiscal-period';
    protected static bool $shouldRegisterNavigation = false;

    public ?int $fiscal_period_id = null;

    public function form(Schema $form): Schema
    {
        return $form->schema([
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
        session(['active_fiscal_period_id' => $data['fiscal_period_id']]);

        $period = FiscalPeriod::find($data['fiscal_period_id']);

        Notification::make()
            ->title("Período activo: {$period->name}")
            ->success()
            ->send();

        $this->redirect('/admin');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('select')
                ->label('Ingresar al período')
                ->action('select'),
        ];
    }
}
