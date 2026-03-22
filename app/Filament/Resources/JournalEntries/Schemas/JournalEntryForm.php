<?php

namespace App\Filament\Resources\JournalEntries\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class JournalEntryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('entry_date')
                    ->required(),
                TextInput::make('description')
                    ->required(),
                Select::make('fiscal_period_id')
                    ->relationship('fiscalPeriod', 'name')
                    ->required(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('reference_id')
                    ->required()
                    ->numeric(),
                Select::make('reference_type')
                    ->options([
            'sale' => 'Sale',
            'purchase' => 'Purchase',
            'expense' => 'Expense',
            'manual' => 'Manual',
            'adjustment' => 'Adjustment',
        ])
                    ->required(),
            ]);
    }
}
