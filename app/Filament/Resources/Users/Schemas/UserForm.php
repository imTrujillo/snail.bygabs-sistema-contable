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

                        Select::make('role_id')
                            ->label('Rol')
                            ->relationship('role', 'name')
                            ->required()
                            ->prefixIcon('heroicon-m-shield-check'),
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

            ]);
    }
}
