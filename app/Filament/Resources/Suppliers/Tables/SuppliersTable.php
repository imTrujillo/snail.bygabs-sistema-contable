<?php

namespace App\Filament\Resources\Suppliers\Tables;

use App\Filament\Exports\SupplierExporter;
use App\Filament\Imports\SupplierImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class SuppliersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-building-storefront')
                    ->description(fn($record) => collect([
                        $record->nrc ? 'NRC: ' . $record->nrc : null,
                        $record->nit ? 'NIT: ' . $record->nit : null,
                    ])->filter()->join(' · ')),

                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->copyable()
                    ->copyMessage('Teléfono copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('email')
                    ->label('Correo')
                    ->searchable()
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->copyMessage('Email copiado')
                    ->copyMessageDuration(1500),

                TextColumn::make('purchases_count')
                    ->label('# Compras')
                    ->counts('purchases')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                TextColumn::make('nrc')
                    ->label('NRC')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nit')
                    ->label('NIT')
                    ->searchable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('name', 'asc')

            ->filters([
                Filter::make('has_nit')
                    ->label('Con NIT registrado')
                    ->query(fn($query) => $query->whereNotNull('nit')->where('nit', '!=', '')),

                Filter::make('has_purchases')
                    ->label('Con compras registradas')
                    ->query(fn($query) => $query->has('purchases')),

                Filter::make('no_purchases')
                    ->label('Sin compras aún')
                    ->query(fn($query) => $query->doesntHave('purchases')),
            ])

            ->headerActions([
                ImportAction::make()
                    ->importer(SupplierImporter::class)
                    ->label('Importar'),

                ExportAction::make()
                    ->exporter(SupplierExporter::class)
                    ->label('Exportar'),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(SupplierExporter::class),
                ]),
            ])

            ->paginated([10, 25, 50])

            ->extremePaginationLinks();
    }
}
