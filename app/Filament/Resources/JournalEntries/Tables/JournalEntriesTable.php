<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use App\Filament\Exports\JournalEntryExporter;
use App\Filament\Imports\JournalEntryImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JournalEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('entry_date')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable()
                    ->icon('heroicon-m-calendar'),

                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn($record) => $record->description),

                TextColumn::make('fiscalPeriod.name')
                    ->label('Período Fiscal')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('reference_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sale'       => 'success',
                        'purchase'   => 'warning',
                        'expense'    => 'danger',
                        'manual'     => 'gray',
                        'adjustment' => 'info',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sale'       => 'Venta',
                        'purchase'   => 'Compra',
                        'expense'    => 'Gasto',
                        'manual'     => 'Manual',
                        'adjustment' => 'Ajuste',
                        default      => $state,
                    }),

                TextColumn::make('reference_id')
                    ->label('Ref. ID')
                    ->sortable()
                    ->prefix('#')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->sortable()
                    ->icon('heroicon-m-user')
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

            ->defaultSort('entry_date', 'desc')

            ->headerActions([
                ImportAction::make()
                    ->importer(JournalEntryImporter::class)
                    ->label('Importar'),

                ExportAction::make()
                    ->exporter(JournalEntryExporter::class)
                    ->label('Exportar'),
            ])

            ->filters([
                SelectFilter::make('reference_type')
                    ->label('Tipo de Referencia')
                    ->options([
                        'sale'       => 'Venta',
                        'purchase'   => 'Compra',
                        'expense'    => 'Gasto',
                        'manual'     => 'Manual',
                        'adjustment' => 'Ajuste',
                    ]),

                SelectFilter::make('fiscal_period_id')
                    ->label('Período Fiscal')
                    ->relationship('fiscalPeriod', 'name')
                    ->searchable()
                    ->preload(),
            ])

            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()->requiresConfirmation(),
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
