<?php

namespace App\Filament\Resources\Expenses\Tables;

use App\Filament\Exports\ExpenseExporter;
use App\Filament\Imports\ExpenseImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('description')
                    ->label('Descripción')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-receipt-percent')
                    ->description(fn($record) => $record->category),

                TextColumn::make('amount')
                    ->label('Monto')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        \Filament\Tables\Columns\Summarizers\Sum::make()
                            ->money('USD')
                            ->label('Total'),
                    ]),

                BadgeColumn::make('paid_with')
                    ->label('Método')
                    ->colors([
                        'success' => 'Efectivo',
                        'info'    => 'Transferencia',
                        'warning' => 'Tarjeta',
                    ])
                    ->icons([
                        'heroicon-m-banknotes'        => 'Efectivo',
                        'heroicon-m-arrow-right-circle' => 'Transferencia',
                        'heroicon-m-credit-card'      => 'Tarjeta',
                    ]),

                TextColumn::make('account.name')
                    ->label('Cuenta')
                    ->sortable()
                    ->icon('heroicon-m-building-library')
                    ->toggleable(),

                TextColumn::make('expense_date')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(
                        fn($record): string => $record->expense_date
                            ? $record->expense_date->diffForHumans()
                            : ''
                    ),

                TextColumn::make('notes')
                    ->label('Notas')
                    ->limit(35)
                    ->tooltip(fn($record) => $record->notes)
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

            ->defaultSort('expense_date', 'desc')

            ->filters([
                SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'Operativo'      => 'Operativo',
                        'Administrativo' => 'Administrativo',
                        'Marketing'      => 'Marketing',
                        'Nomina'         => 'Nómina',
                        'Servicios'      => 'Servicios',
                        'Alquiler'       => 'Alquiler',
                        'Transporte'     => 'Transporte',
                        'Insumos'        => 'Insumos / Materiales',
                        'Impuestos'      => 'Impuestos',
                        'Otros'          => 'Otros',
                    ]),

                SelectFilter::make('paid_with')
                    ->label('Método de pago')
                    ->options([
                        'Efectivo'      => 'Efectivo',
                        'Transferencia' => 'Transferencia',
                        'Tarjeta'       => 'Tarjeta',
                    ]),

                SelectFilter::make('account_id')
                    ->label('Cuenta')
                    ->relationship('account', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('expense_date')
                    ->label('Rango de fechas')
                    ->form([
                        DatePicker::make('from')
                            ->label('Desde')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('until')
                            ->label('Hasta')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('expense_date', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('expense_date', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Desde: ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Hasta: ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y');
                        }
                        return $indicators;
                    }),
            ])

            ->filtersFormColumns(3)

            ->headerActions([
                ImportAction::make()
                    ->importer(ExpenseImporter::class)
                    ->label('Importar'),

                ExportAction::make()
                    ->exporter(ExpenseExporter::class)
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
                        ->exporter(ExpenseExporter::class),
                ]),
            ])

            ->paginated([10, 25, 50])

            ->extremePaginationLinks();
    }
}
