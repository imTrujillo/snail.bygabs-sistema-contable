<?php

namespace App\Filament\Resources\FiscalPeriods\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FiscalPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Período Fiscal')
                    ->description('Define el rango de fechas y nombre del período contable.')
                    ->icon('heroicon-o-calendar')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del período')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Ej: Enero 2025, Q1 2025, Ejercicio 2025...')
                            ->columnSpanFull(),

                        DatePicker::make('start_date')
                            ->label('Fecha de inicio')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $set('end_date', \Carbon\Carbon::parse($state)->endOfMonth()->toDateString());
                                }
                            })
                            ->columnSpan(1),

                        DatePicker::make('end_date')
                            ->label('Fecha de cierre')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->afterOrEqual('start_date')
                            ->columnSpan(1),
                    ]),

                Section::make('Estado del Período')
                    ->description('Un período cerrado no permite registrar nuevas transacciones.')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Toggle::make('is_closed')
                            ->label('Período cerrado')
                            ->helperText('Al cerrar el período, no se podrán registrar ni modificar transacciones dentro de este rango de fechas.')
                            ->onIcon('heroicon-m-lock-closed')
                            ->offIcon('heroicon-m-lock-open')
                            ->onColor('danger')
                            ->offColor('success')
                            ->default(false),
                    ]),

            ]);
    }
}
