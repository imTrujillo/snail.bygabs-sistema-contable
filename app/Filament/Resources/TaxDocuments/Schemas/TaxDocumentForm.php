<?php

namespace App\Filament\Resources\TaxDocuments\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaxDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->options([
            'FCF' => 'F c f',
            'CCF' => 'C c f',
            'NC_CCF' => 'N c  c c f',
            'ND_CCF' => 'N d  c c f',
            'NC_FCF' => 'N c  f c f',
        ])
                    ->required(),
                TextInput::make('series')
                    ->required(),
                TextInput::make('correlative_number')
                    ->required()
                    ->numeric(),
                TextInput::make('document_number')
                    ->required(),
                TextInput::make('issue_date')
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->default(null),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->default(null),
                TextInput::make('reference_id')
                    ->required()
                    ->numeric(),
                Select::make('reference_type')
                    ->options(['sale' => 'Sale', 'purchase' => 'Purchase'])
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
                TextInput::make('iva_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                Toggle::make('is_voided')
                    ->required(),
                DateTimePicker::make('voided_at')
                    ->required(),
            ]);
    }
}
