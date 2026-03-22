<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('unit')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('stock')
                    ->required()
                    ->numeric(),
                TextInput::make('cost_price')
                    ->required()
                    ->numeric(),
                TextInput::make('sale_price')
                    ->required()
                    ->numeric(),
            ]);
    }
}
