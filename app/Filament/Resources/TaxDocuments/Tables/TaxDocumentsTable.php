<?php

namespace App\Filament\Resources\TaxDocuments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TaxDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type'),
                TextColumn::make('series')
                    ->searchable(),
                TextColumn::make('correlative_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('document_number')
                    ->searchable(),
                TextColumn::make('issue_date')
                    ->searchable(),
                TextColumn::make('customer.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reference_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reference_type'),
                TextColumn::make('exempt_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('non_taxable_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxable_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('iva_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_voided')
                    ->boolean(),
                TextColumn::make('voided_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
