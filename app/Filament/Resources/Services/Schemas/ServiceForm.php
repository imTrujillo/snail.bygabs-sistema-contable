<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Información del Servicio')
                    ->description('Datos generales del servicio ofrecido.')
                    ->icon('heroicon-o-sparkles')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del servicio')
                            ->required()
                            ->minLength(3)
                            ->maxLength(255)
                            ->unique(table: 'services', column: 'name', ignoreRecord: true)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descripción')
                            ->required()
                            ->rows(3)
                            ->maxLength(1000)
                            ->placeholder('Describe qué incluye este servicio...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Precio y Duración')
                    ->description('Cuánto cuesta y cuánto tiempo toma.')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')
                            ->label('Precio')
                            ->required()
                            ->numeric()
                            ->minValue(0.01)
                            ->maxValue(99999.99)
                            ->step(0.01)
                            ->prefix('$'),

                        TextInput::make('duration_minutes')
                            ->label('Duración (minutos)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(480)
                            ->integer()
                            ->suffix('min'),
                    ]),

            ]);
    }
}
