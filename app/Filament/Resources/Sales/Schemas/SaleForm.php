<?php

namespace App\Filament\Resources\Sales\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SaleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->required(),
                Select::make('appointment_id')
                    ->relationship('appointment', 'id')
                    ->default(null),
                Select::make('tax_document_id')
                    ->relationship('taxDocument', 'id')
                    ->default(null),
                TextInput::make('total')
                    ->required()
                    ->numeric(),
                Select::make('payment_method')
                    ->options(['Efectivo' => 'Efectivo', 'Transferencia' => 'Transferencia', 'Tarjeta' => 'Tarjeta'])
                    ->required(),
            ]);
    }
}
