<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('Información del Asiento')
                ->description('Datos generales del asiento contable.')
                ->icon('heroicon-o-book-open')
                ->columns(2)
                ->schema([
                    DatePicker::make('entry_date')
                        ->label('Fecha del asiento')
                        ->required()
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->default(now())
                        ->columnSpan(1),

                    Select::make('fiscal_period_id')
                        ->label('Período fiscal')
                        ->relationship(
                            name: 'fiscalPeriod',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn($query) => $query->where('is_closed', false), // ✅ solo períodos abiertos
                        )
                        ->required()
                        ->searchable()
                        ->preload()
                        ->prefixIcon('heroicon-m-calendar-days')
                        ->columnSpan(1),

                    Select::make('journal_entry_type_id') // ✅ nuevo
                        ->label('Tipo de asiento')
                        ->relationship('journalEntryType', 'name')
                        ->required()
                        ->prefixIcon('heroicon-m-tag')
                        ->columnSpan(1),

                    Select::make('reference_type')
                        ->label('Tipo de referencia')
                        ->options([
                            'sale'       => 'Venta',
                            'purchase'   => 'Compra',
                            'expense'    => 'Gasto',
                            'manual'     => 'Manual',
                            'adjustment' => 'Ajuste',
                        ])
                        ->prefixIcon('heroicon-m-arrows-right-left')
                        ->columnSpan(1),

                    TextInput::make('description')
                        ->label('Descripción')
                        ->required()
                        ->maxLength(500)
                        ->placeholder('Ej: Registro de venta de servicios')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
