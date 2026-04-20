<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;


class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Información del Cliente')
                ->icon('heroicon-o-user')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required()
                        ->minLength(3)
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->required()
                        ->regex('/^[67]\d{7}$/') // formato El Salvador: 8 dígitos, empieza en 6 o 7
                        ->maxLength(8)
                        ->helperText('Ej: 71234567'),


                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->unique(table: 'customers', column: 'email', ignoreRecord: true)
                        ->maxLength(255),

                    Textarea::make('notes')
                        ->label('Notas')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),

            Section::make('Datos Fiscales')
                ->description('Requerido si el cliente emite Crédito Fiscal (CCF).')
                ->icon('heroicon-o-document-check')
                ->columns(2)
                ->schema([
                    Toggle::make('is_contributor')
                        ->label('Es contribuyente (emite CCF)')
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('nrc')
                        ->label('NRC')
                        ->visible(fn(Get $get) => $get('is_contributor'))
                        ->required(fn(Get $get) => $get('is_contributor'))
                        ->unique(table: 'customers', column: 'nrc', ignoreRecord: true)
                        ->regex('/^\d{1,6}-\d$/')
                        ->helperText('Formato: 123456-7')
                        ->maxLength(20),

                    TextInput::make('nit')
                        ->label('NIT')
                        ->visible(fn(Get $get) => $get('is_contributor'))
                        ->required(fn(Get $get) => $get('is_contributor'))
                        ->unique(table: 'customers', column: 'nit', ignoreRecord: true)
                        ->regex('/^\d{4}-\d{6}-\d{3}-\d$/')
                        ->helperText('Formato: 0614-290786-102-3')
                        ->maxLength(17)
                ]),
        ]);
    }
}
