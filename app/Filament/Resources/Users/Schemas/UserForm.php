<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required(),

                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->nullable(),
                    ]),

                Section::make('Credenciales')
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn($context) => $context === 'create')
                            ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state)),
                    ]),

                Section::make('Rol y Salario')
                    ->columns(2)
                    ->schema([
                        Select::make('role')
                            ->label('Rol')
                            ->options(Role::all()->pluck('name', 'id'))
                            ->required(),

                        TextInput::make('salary')
                            ->label('Salario')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->default(0),

                        Select::make('salary_type')
                            ->label('Frecuencia de pago')
                            ->options([
                                'Mensual'    => 'Mensual',
                                'Quincenal'  => 'Quincenal',
                                'Semanal'    => 'Semanal',
                            ])
                            ->default('Mensual'),
                    ]),
            ]);
    }
}
