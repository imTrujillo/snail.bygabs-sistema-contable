<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\UserRole;
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
                    ->schema([
                        TextInput::make('name')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email address')
                            ->email()
                            ->required(),
                    ]),

                Section::make('Credenciales')
                    ->schema([
                        TextInput::make('password')
                            ->password()
                            ->required(),
                    ]),

                Section::make('Rol y Configuración')
                    ->schema([
                        Select::make('role')
                            ->options(UserRole::class)
                            ->required(),
                    ]),
            ]);
    }
}
