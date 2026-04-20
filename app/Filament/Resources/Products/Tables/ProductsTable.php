<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Exports\ProductExporter;
use App\Filament\Imports\ProductImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->headerActions([
                ImportAction::make()
                    ->importer(ProductImporter::class)
                    ->label('Importar'),
                ExportAction::make()
                    ->exporter(ProductExporter::class)
                    ->label('Exportar'),
            ])
            ->columns([
                TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-cube')
                    ->description(fn($record) => $record->unit),

                TextColumn::make('stock')
                    ->label('Stock')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn($state): string => match (true) {
                        $state <= 0  => 'danger',
                        $state <= 5  => 'warning',
                        default      => 'success',
                    }),

                TextColumn::make('cost_price')
                    ->label('Costo')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Creado')
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
                SelectFilter::make('unit')
                    ->label('Unidad')
                    ->options([
                        'unidad'   => 'Unidad',
                        'kg'       => 'Kilogramo',
                        'g'        => 'Gramo',
                        'lb'       => 'Libra',
                        'l'        => 'Litro',
                        'ml'       => 'Mililitro',
                        'caja'     => 'Caja',
                        'paquete'  => 'Paquete',
                        'servicio' => 'Servicio',
                        'hora'     => 'Hora',
                    ]),

                Filter::make('low_stock')
                    ->label('Stock bajo (≤ 5)')
                    ->query(fn($query) => $query->where('stock', '<=', 5)->where('stock', '>', 0)),

                Filter::make('out_of_stock')
                    ->label('Sin stock')
                    ->query(fn($query) => $query->where('stock', '<=', 0)),
            ])

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->paginated([10, 25, 50])

            ->extremePaginationLinks();
    }
}
