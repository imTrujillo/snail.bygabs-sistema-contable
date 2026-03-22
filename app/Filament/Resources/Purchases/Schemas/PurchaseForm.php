<?php

namespace App\Filament\Resources\Purchases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->required(),
                DatePicker::make('purchase_date')
                    ->required(),
                TextInput::make('exempt_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('non_taxable_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('taxable_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('credit_fiscal')
                    ->required()
                    ->numeric(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                Select::make('account_id')
                    ->relationship('account', 'name')
                    ->required(),
                TextInput::make('notes')
                    ->required(),


                TextInput::make('document_number')   // ← ej: "CCF-001234"
                    ->label('Número de documento')
                    ->required(),

                Select::make('document_type')
                    ->label('Tipo de documento')
                    ->options([
                        'CCF'  => 'Crédito Fiscal (CCF)',
                        'FCF'  => 'Factura Consumidor Final'
                    ])
                    ->required(),
            ]);
    }
}
