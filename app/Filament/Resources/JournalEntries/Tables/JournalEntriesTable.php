<?php

namespace App\Filament\Resources\JournalEntries\Tables;

use App\Filament\Exports\JournalEntryExporter;
use Filament\Actions\ExportAction;
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
                    ->tooltip(fn ($record) => $record->description),

                TextColumn::make('journalEntryType.name')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Apertura' => 'info',
                        'Diario' => 'success',
                        'Ajuste' => 'warning',
                        'Cierre' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('fiscalPeriod.name')
                    ->label('Período Fiscal')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('reference_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'sale' => 'success',
                        'purchase' => 'warning',
                        'expense' => 'danger',
                        'manual' => 'gray',
                        'adjustment' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'sale' => 'Venta',
                        'purchase' => 'Compra',
                        'expense' => 'Gasto',
                        'manual' => 'Manual',
                        'adjustment' => 'Ajuste',
                        default => $state,
                    }),

                TextColumn::make('reference_id')
                    ->label('Ref. ID')
                    ->sortable()
                    ->prefix('#')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('lines_sum_debit')
                    ->label('Total débitos')
                    ->sum('lines', 'debit')
                    ->money('USD')
                    ->tooltip('Suma de todas las líneas al debe de esta partida. En una partida cuadrada coincide con el total de créditos.'),

                TextColumn::make('lines_sum_credit')
                    ->label('Total créditos')
                    ->sum('lines', 'credit')
                    ->money('USD')
                    ->tooltip('Suma de todas las líneas al haber. Para ventas: efectivo/banco al debe; ventas e IVA al haber — ambos totales deben ser iguales.'),

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
                ExportAction::make()
                    ->exporter(JournalEntryExporter::class)
                    ->label('Exportar'),
            ])

            ->filters([
                SelectFilter::make('reference_type')
                    ->label('Tipo de Referencia')
                    ->options([
                        'sale' => 'Venta',
                        'purchase' => 'Compra',
                        'expense' => 'Gasto',
                        'manual' => 'Manual',
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
            ])

            ->paginated([10, 25, 50])

            ->extremePaginationLinks();
    }
}
