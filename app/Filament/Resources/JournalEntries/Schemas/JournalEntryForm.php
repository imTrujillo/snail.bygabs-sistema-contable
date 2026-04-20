<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use App\Models\FiscalPeriod;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));

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
                        ->default($period?->start_date ?? now())
                        ->minDate($period?->start_date ?? now())   // ← dentro del período
                        ->maxDate($period?->end_date ?? now())
                        ->columnSpan(1),

                    Select::make('fiscal_period_id')
                        ->label('Período fiscal')
                        ->relationship(
                            name: 'fiscalPeriod',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn($query) => $query->where('is_closed', false),
                        )
                        ->default($period?->id)
                        ->required()
                        ->searchable()
                        ->preload()
                        ->disabled()       // igual que planilla, no se cambia manualmente
                        ->dehydrated()
                        ->prefixIcon('heroicon-m-calendar-days')
                        ->columnSpan(1),

                    Select::make('journal_entry_type_id')
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
                        ->default('manual')
                        ->required()
                        ->prefixIcon('heroicon-m-arrows-right-left')
                        ->columnSpan(1),

                    TextInput::make('description')
                        ->label('Descripción')
                        ->required()
                        ->minLength(5)
                        ->maxLength(500)
                        ->placeholder('Ej: Registro de venta de servicios')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
