<?php

namespace App\Filament\Pages\Settings;

use App\Models\CompanySetting as ModelsCompanySetting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CompanySetting extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|UnitEnum|null $navigationGroup = 'Configuración';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $title = 'Mi Empresa';

    protected static ?string $navigationLabel = 'Mi Empresa';

    protected string $view = 'filament.pages.settings.company-setting';

    public ?array $data = [];

    public function mount(): void
    {
        $company = ModelsCompanySetting::current();

        $this->form->fill([
            'name' => $company->name,
            'nrc' => $company->nrc,
            'nit' => $company->nit,
            'address' => $company->address,
            'tax_regime' => $company->tax_regime,
            'logo' => ($company->logo && $company->logo !== '')
                ? [$company->logo]
                : [],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Identificación')
                    ->description('Datos usados en encabezados de reportes (Libros de compras/ventas, balances). El NRC y NIT son opcionales hasta que estén definitivos.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre comercial / Razón social')
                            ->required()
                            ->minLength(2)
                            ->maxLength(255),

                        TextInput::make('nrc')
                            ->label('N.R.C.')
                            ->maxLength(50)
                            ->placeholder('Ej.: 123456-7')
                            ->rules([
                                'nullable',
                                'string',
                                'max:50',
                                'regex:/^(\d{1,7}-\d)?$/',
                            ])
                            ->validationMessages([
                                'regex' => 'Use el formato de NRC habitual (números-guión-dígito verificador), sin espacios.',
                            ]),

                        TextInput::make('nit')
                            ->label('N.I.T.')
                            ->maxLength(40)
                            ->placeholder('Opcional (ej.: 0614-251289-101-1)')
                            ->rules([
                                'nullable',
                                'string',
                                'max:40',
                                'regex:/^[\d\-\.\s]*$/',
                            ])
                            ->validationMessages([
                                'regex' => 'Solo números, guiones, puntos y espacios.',
                            ]),

                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(500)
                            ->rules(['nullable', 'string', 'max:500'])
                            ->placeholder('Opcional'),

                        Select::make('tax_regime')
                            ->label('Régimen fiscal')
                            ->placeholder('Sin definir / referencia interna')
                            ->native(false)
                            ->options([
                                'Consumidor Final' => 'Consumidor Final',
                                'Contribuyente' => 'Contribuyente (crédito fiscal)',
                            ])
                            ->nullable()
                            ->helperText(
                                'En El Salvador indica la clasificación tributaria de la empresa (por ejemplo, si emite comprobantes '
                                .'con derecho a crédito fiscal o actúa principalmente como consumidor final). '
                                .'En esta aplicación no altera por sí solo el tipo FCF/CCF de cada venta: eso depende del cliente y del '
                                .'documento elegido. El valor queda como referencia para reportes y posible lógica futura.'
                            ),

                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/jpg', 'image/webp'])
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('logos')
                            ->visibility('public')
                            ->nullable()
                            ->helperText('Opcional. PNG, JPG o WebP. Máx. 2 MB.'),
                    ])
                    ->columns(2),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (['nrc', 'nit', 'address', 'tax_regime'] as $key) {
            if (array_key_exists($key, $data) && $data[$key] === '') {
                $data[$key] = null;
            }
        }

        $logo = $data['logo'] ?? null;
        if (is_array($logo)) {
            $first = collect($logo)->filter(fn ($v) => $v !== null && $v !== '')->first();
            $data['logo'] = $first;
        } elseif ($logo === '' || $logo === null) {
            $data['logo'] = null;
        }

        $company = ModelsCompanySetting::current();
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
