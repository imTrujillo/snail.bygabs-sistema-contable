<?php

namespace App\Filament\Resources\FiscalPeriods\Schemas;

use App\Models\FiscalPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FiscalPeriodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Período Fiscal')
                ->icon('heroicon-o-calendar')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre del período')
                        ->required()
                        ->minLength(3)
                        ->maxLength(100)
                        ->unique(table: 'fiscal_periods', column: 'name', ignoreRecord: true)
                        ->placeholder('Ej: Enero 2025')
                        ->columnSpanFull(),

                    DatePicker::make('start_date')
                        ->label('Fecha de inicio')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->live()
                        ->rules(['date'])
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $set('end_date', \Carbon\Carbon::parse($state)->endOfMonth()->toDateString());
                            }
                        })
                        // No debe solapar otro período existente
                        ->rules([
                            fn($get, $record) => function ($attribute, $value, $fail) use ($get, $record) {
                                $overlaps = FiscalPeriod::where('id', '!=', $record?->id)
                                    ->where(function ($q) use ($value, $get) {
                                        $q->whereBetween('start_date', [$value, $get('end_date')])
                                            ->orWhereBetween('end_date', [$value, $get('end_date')]);
                                    })->exists();

                                if ($overlaps) {
                                    $fail('Las fechas se solapan con otro período fiscal existente.');
                                }
                            }
                        ])
                        ->columnSpan(1),

                    DatePicker::make('end_date')
                        ->label('Fecha de cierre')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->afterOrEqual('start_date')
                        ->rules(['date'])
                        ->columnSpan(1),
                ]),

            Section::make('Estado')
                ->icon('heroicon-o-lock-closed')
                ->schema([
                    Toggle::make('is_closed')
                        ->label('Período cerrado')
                        ->disabled()
                        ->helperText('Usa la acción "Cerrar Período" desde la tabla.'),
                ]),
        ]);
    }
}
