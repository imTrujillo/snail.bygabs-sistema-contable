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
        return $schema
            ->components([

                Section::make('Información del Asiento')
                    ->description('Datos generales del asiento contable.')
                    ->icon('heroicon-o-book-open')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('entry_date')
                            ->label('Fecha del Asiento')
                            ->required()
                            ->prefixIcon('heroicon-m-calendar')
                            ->columnSpan(1),

                        Select::make('fiscal_period_id')
                            ->label('Período Fiscal')
                            ->relationship('fiscalPeriod', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-m-calendar-days')
                            ->columnSpan(1),

                        TextInput::make('description')
                            ->label('Descripción')
                            ->required()
                            ->maxLength(500)
                            ->placeholder('Ej: Registro de compra de mercadería')
                            ->prefixIcon('heroicon-m-chat-bubble-left-ellipsis')
                            ->columnSpanFull(),
                    ]),

                Section::make('Referencia')
                    ->description('Documento o transacción que origina este asiento.')
                    ->icon('heroicon-o-link')
                    ->columns(2)
                    ->schema([
                        Select::make('reference_type')
                            ->label('Tipo de Referencia')
                            ->required()
                            ->options([
                                'sale'       => 'Venta',
                                'purchase'   => 'Compra',
                                'expense'    => 'Gasto',
                                'manual'     => 'Manual',
                                'adjustment' => 'Ajuste',
                            ])
                            ->prefixIcon('heroicon-m-arrows-right-left')
                            ->columnSpan(1),

                        TextInput::make('reference_id')
                            ->label('ID de Referencia')
                            ->required()
                            ->numeric()
                            ->prefixIcon('heroicon-m-finger-print')
                            ->columnSpan(1),
                    ]),

            ]);
    }
}
