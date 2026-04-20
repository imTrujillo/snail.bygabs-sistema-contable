<?php

namespace App\Filament\Resources\Suppliers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información del Proveedor')
                    ->description('Datos generales y fiscales del proveedor.')
                    ->icon('heroicon-o-building-storefront')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre / Razón social')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('nrc')
                            ->label('NRC')
                            ->required()
                            ->unique(table: 'suppliers', column: 'nrc', ignoreRecord: true)
                            ->regex('/^\d{1,6}-\d$/')
                            ->helperText('Formato: 123456-7')
                            ->maxLength(20),

                        TextInput::make('nit')
                            ->label('NIT')
                            ->unique(table: 'suppliers', column: 'nit', ignoreRecord: true)
                            ->regex('/^\d{4}-\d{6}-\d{3}-\d$/')
                            ->helperText('Formato: 0614-123456-001-0')
                            ->maxLength(20),
                    ]),

                Section::make('Contacto')
                    ->description('Información de contacto del proveedor.')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->required()
                            ->tel()
                            ->regex('/^[67]\d{7}$/') // formato El Salvador: 8 dígitos, empieza en 6 o 7
                            ->maxLength(8)
                            ->helperText('Ej: 71234567'),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->unique(table: 'suppliers', column: 'email', ignoreRecord: true)
                            ->maxLength(255),
                    ]),

            ]);
    }
}
