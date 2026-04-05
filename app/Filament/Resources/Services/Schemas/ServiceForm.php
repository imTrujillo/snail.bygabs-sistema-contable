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
                            ->maxLength(255)
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
                            ->minValue(0)
                            ->step(0.01)
                            ->prefix('$')
                            ->columnSpan(1),

                        TextInput::make('duration_minutes')
                            ->label('Duración (minutos)')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->suffix('min')
                            ->helperText(
                                fn($state): string =>
                                $state > 0
                                    ? floor($state / 60) . 'h ' . ($state % 60) . 'min'
                                    : ''
                            )
                            ->columnSpan(1),
                    ]),

            ]);
    }
}
