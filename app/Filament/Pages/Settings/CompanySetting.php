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
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CompanySetting extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Mi Empresa';

    protected string $view = 'filament.pages.settings.company-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $company = ModelsCompanySetting::current();

        $this->form->fill([
            'name'       => $company->name,
            'nrc'        => $company->nrc,
            'nit'        => $company->nit,
            'address'    => $company->address,
            'tax_regime' => $company->tax_regime,
            'logo'       => ($company->logo && $company->logo !== 'i')
                ? [$company->logo]
                : [],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->statePath('data')
            ->schema([
                TextInput::make('name')->label('Nombre de la empresa')->required(),
                TextInput::make('nrc')->label('N.R.C.'),
                TextInput::make('nit')->label('N.I.T.'),
                TextInput::make('address')->label('Dirección'),
                Select::make('tax_regime')
                    ->label('Régimen fiscal')
                    ->options([
                        'Consumidor Final' => 'Consumidor Final',
                        'Contribuyente'    => 'Contribuyente',
                    ]),
                FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                    ->disk('public')
                    ->directory('logos')
                    ->visibility('public'),
            ])->columns(2);
    }

    public function save(): void
    {
        $company = ModelsCompanySetting::current();
        $data = $this->form->getState();

        $logo = $data['logo'] ?? null;
        $data['logo'] = is_array($logo)
            ? (collect($logo)->first() ?? $company->logo)
            : ($logo ?: $company->logo);

        $company->update($data);

        Notification::make()
            ->title('Configuración guardada')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin();
    }

    public static function canCreate(): bool
    {
        return Auth::user()?->isAdmin();
    }

    public static function canEdit($record): bool
    {
        return Auth::user()?->isAdmin();
    }

    public static function canDelete($record): bool
    {
        return Auth::user()?->isAdmin();
    }
}
