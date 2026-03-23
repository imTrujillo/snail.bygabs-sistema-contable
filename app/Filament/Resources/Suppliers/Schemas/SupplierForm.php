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
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('nrc')
                            ->label('NRC')
                            ->required()
                            ->maxLength(20)
                            ->placeholder('Ej: 123456-7')
                            ->prefixIcon('heroicon-m-identification')
                            ->helperText('Número de Registro de Contribuyente')
                            ->columnSpan(1),

                        TextInput::make('nit')
                            ->label('NIT')
                            ->default(null)
                            ->maxLength(20)
                            ->placeholder('Ej: 0614-123456-001-0')
                            ->prefixIcon('heroicon-m-finger-print')
                            ->helperText('Número de Identificación Tributaria')
                            ->columnSpan(1),
                    ]),

                Section::make('Contacto')
                    ->description('Información de contacto del proveedor.')
                    ->icon('heroicon-o-phone')
                    ->columns(2)
                    ->schema([
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->prefixIcon('heroicon-m-phone')
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon('heroicon-m-envelope')
                            ->columnSpan(1),
                    ]),

            ]);
    }
}
