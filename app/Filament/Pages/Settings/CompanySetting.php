<?php

namespace App\Filament\Pages\Settings;

use App\Models\CompanySetting as ModelsCompanySetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;
use UnitEnum;

class CompanySetting extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Mi Empresa';

    protected string $view = 'filament.pages.settings.company-setting';

    public ?string $name       = null;
    public ?string $nrc        = null;
    public ?string $nit        = null;
    public ?string $address    = null;
    public ?string $tax_regime = null;
    public array $logo       = [];

    public function mount(): void
    {
        $company = ModelsCompanySetting::current();

        $this->fill([
            'name'       => $company->name,
            'nrc'        => $company->nrc,
            'nit'        => $company->nit,
            'address'    => $company->address,
            'tax_regime' => $company->tax_regime,
            'logo'       => $company->logo ? [$company->logo] : [],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form->schema([
            TextInput::make('name')->label('Nombre de la empresa')->required(),
            TextInput::make('nrc')->label('N.R.C.'),
            TextInput::make('nit')->label('N.I.T.'),
            TextInput::make('address')->label('Dirección'),
            Select::make('tax_regime')
                ->label('Régimen fiscal')
                ->options([
                    'consumidor_final' => 'Consumidor Final',
                    'contribuyente'    => 'Contribuyente',
                ]),
            FileUpload::make('logo')
                ->label('Logo')
                ->image()
                ->disk('public')
                ->directory('/')
                ->visibility('public'),
        ])->columns(2);
    }

    public function save(): void
    {
        $company = ModelsCompanySetting::current();

        $data = $this->form->getState();

        // FileUpload devuelve array, guardamos solo el primer elemento
        $data['logo'] = $data['logo'][0] ?? null;

        $company->update($data);

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }
}
