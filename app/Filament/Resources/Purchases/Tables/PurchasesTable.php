<?php

namespace App\Filament\Resources\Purchases\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('supplier.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxDocument.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('purchase_date')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('exempt_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('non_taxable_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxable_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('credit_fiscal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('account.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('notes')
                    ->searchable(),
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
