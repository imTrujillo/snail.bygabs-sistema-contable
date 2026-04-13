<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Select;
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
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('phone')
                        ->label('Teléfono')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
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
                        ->maxLength(20),

                    TextInput::make('nit')
                        ->label('NIT')
                        ->visible(fn(Get $get) => $get('is_contributor'))
                        ->maxLength(20),
                ]),
        ]);
    }
}
