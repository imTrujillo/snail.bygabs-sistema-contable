<?php

namespace App\Filament\Resources\Expenses\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExpenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('description')
                    ->required(),
                TextInput::make('category')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                DateTimePicker::make('expense_date')
                    ->required(),
                TextInput::make('paid_with')
                    ->required(),
                Select::make('account_id')
                    ->relationship('account', 'name')
                    ->required(),
                TextInput::make('notes')
                    ->required(),
            ]);
    }
}
