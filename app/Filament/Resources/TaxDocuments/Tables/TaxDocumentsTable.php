<?php

namespace App\Filament\Resources\TaxDocuments\Tables;

use App\Filament\Exports\TaxDocumentExporter;
use App\Filament\Imports\TaxDocumentImporter;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\ExportAction;
use Filament\Actions\ExportBulkAction;
use Filament\Actions\ImportAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TaxDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->label('Documento')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-document-text')
                    ->description(fn($record) => collect([
                        $record->series ? 'Serie: ' . $record->series : null,
                        $record->correlative_number ? '#' . $record->correlative_number : null,
                    ])->filter()->join(' · ')),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'FCF'    => 'info',
                        'CCF'    => 'success',
                        'NC_CCF', 'NC_FCF' => 'warning',
                        'ND_CCF' => 'danger',
                        default  => 'gray',
                    }),

                TextColumn::make('issue_date')
                    ->label('Fecha Emisión')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->icon('heroicon-m-building-storefront'),

                TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable()
                    ->placeholder('—')
                    ->icon('heroicon-m-user'),

                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),

                IconColumn::make('is_voided')
                    ->label('Anulado')
                    ->boolean()
                    ->trueIcon('heroicon-m-x-circle')
                    ->falseIcon('heroicon-m-check-circle')
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('exempt_amount')
                    ->label('Exento')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('non_taxable_amount')
                    ->label('No Sujeto')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('taxable_amount')
                    ->label('Gravado')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('iva_amount')
                    ->label('IVA')
                    ->money('USD')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('voided_at')
                    ->label('Anulado el')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
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

            ->defaultSort('issue_date', 'desc')

            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo de Documento')
                    ->options([
                        'FCF'    => 'Factura Consumidor Final (FCF)',
                        'CCF'    => 'Comprobante de Crédito Fiscal (CCF)',
                        'NC_CCF' => 'Nota de Crédito sobre CCF',
                        'ND_CCF' => 'Nota de Débito sobre CCF',
                        'NC_FCF' => 'Nota de Crédito sobre FCF',
                    ]),

                SelectFilter::make('reference_type')
                    ->label('Referencia')
                    ->options([
                        'sale'     => 'Venta',
                        'purchase' => 'Compra',
                    ]),

                Filter::make('is_voided')
                    ->label('Solo anulados')
                    ->query(fn($query) => $query->where('is_voided', true)),

                Filter::make('active')
                    ->label('Solo vigentes')
                    ->query(fn($query) => $query->where('is_voided', false)),
            ])

            ->headerActions([
                ExportAction::make()
                    ->exporter(TaxDocumentExporter::class)
                    ->label('Exportar'),
            ])

            ->recordActions([
                Action::make('anular')
                    ->label('Anular')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('¿Anular documento?')
                    ->modalDescription('Esta acción no se puede deshacer. El documento quedará marcado como anulado.')
                    ->modalSubmitActionLabel('Sí, anular')
                    ->action(fn($record) => $record->update([
                        'is_voided' => true,
                        'voided_at' => now(),
                    ]))
                    ->visible(fn($record) => ! $record->is_voided),
            ])


            ->toolbarActions([
                BulkActionGroup::make([
                    ExportBulkAction::make()
                        ->exporter(TaxDocumentExporter::class),
                ]),
            ])

            ->paginated([10, 25, 50])

            ->extremePaginationLinks();
    }
}
