<?php

namespace App\Filament\Resources\Sales\Tables;

use App\Filament\Exports\SaleExporter;
use App\Models\Sale;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user')
                    ->description(
                        fn ($record) => $record->appointment
                            ? '📅 '.$record->appointment->appointment_date?->format('d/m/Y H:i')
                            : 'Sin cita asociada'
                    ),

                TextColumn::make('document_type')
                    ->label('Documento')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'FCF' => 'info',
                        'CCF' => 'warning',
                    }),

                TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable()
                    ->summarize([
                        Sum::make()
                            ->money('USD')
                            ->label('Total ventas'),
                    ]),

                TextColumn::make('payment_method')
                    ->label('Pago')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'Efectivo' => 'success',
                        'Transferencia' => 'info',
                        'Tarjeta' => 'warning',
                    })
                    ->icons([
                        'heroicon-m-banknotes' => 'Efectivo',
                        'heroicon-m-arrow-right-circle' => 'Transferencia',
                        'heroicon-m-credit-card' => 'Tarjeta',
                    ]),

                TextColumn::make('taxDocument.id')
                    ->label('Doc. fiscal')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Fecha venta')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(
                        fn ($record): string => $record->created_at
                            ? $record->created_at->diffForHumans()
                            : ''
                    ),

                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('created_at', 'desc')

            ->filters([
                SelectFilter::make('payment_method')
                    ->label('Método de pago')
                    ->options([
                        'Efectivo' => 'Efectivo',
                        'Transferencia' => 'Transferencia',
                        'Tarjeta' => 'Tarjeta',
                    ]),

                SelectFilter::make('document_type')
                    ->label('Tipo de documento')
                    ->options([
                        'FCF' => 'Consumidor Final (FCF)',
                        'CCF' => 'Crédito Fiscal (CCF)',
                    ]),

                SelectFilter::make('customer_id')
                    ->label('Cliente')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
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
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'Desde: '.Carbon::parse($data['from'])->format('d/m/Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Hasta: '.Carbon::parse($data['until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

                Filter::make('has_appointment')
                    ->label('Con cita asociada')
                    ->query(fn ($query) => $query->whereNotNull('appointment_id')),

                Filter::make('has_tax_document')
                    ->label('Con documento fiscal')
                    ->query(fn ($query) => $query->whereNotNull('tax_document_id')),
            ])

            ->filtersFormColumns(3)

            ->headerActions([
                ExportAction::make()
                    ->exporter(SaleExporter::class)
                    ->label('Exportar'),
            ])

            ->recordActions([
                ViewAction::make(),
                Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Sale $record) => ($record->status ?? 'vigente') !== 'anulada')
                    ->action(fn (Sale $record) => $record->anular()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exporter(SaleExporter::class),
                ]),
            ])

            ->paginated([10, 25, 50])

            ->extremePaginationLinks();
    }
}
