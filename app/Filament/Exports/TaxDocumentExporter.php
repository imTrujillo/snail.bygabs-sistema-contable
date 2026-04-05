<?php

namespace App\Filament\Exports;

use App\Models\TaxDocument;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TaxDocumentExporter extends Exporter
{
    protected static ?string $model = TaxDocument::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('type')->label('Tipo'),
            ExportColumn::make('series')->label('Serie'),
            ExportColumn::make('correlative_number')->label('Correlativo'),
            ExportColumn::make('document_number')->label('Número Documento'),
            ExportColumn::make('issue_date')->label('Fecha Emisión')
                ->getStateUsing(fn($record) => $record->issue_date?->format('d/m/Y')),
            ExportColumn::make('customer.name')->label('Cliente'),
            ExportColumn::make('supplier.name')->label('Proveedor'),
            ExportColumn::make('reference_id')->label('ID Referencia'),
            ExportColumn::make('reference_type')->label('Tipo Referencia'),
            ExportColumn::make('exempt_amount')->label('Monto Exento'),
            ExportColumn::make('non_taxable_amount')->label('Monto No Gravado'),
            ExportColumn::make('taxable_amount')->label('Monto Gravado'),
            ExportColumn::make('iva_amount')->label('IVA'),
            ExportColumn::make('total_amount')->label('Total'),
            ExportColumn::make('is_voided')->label('Anulado')
                ->getStateUsing(fn($record) => $record->is_voided ? 'Sí' : 'No'),
            ExportColumn::make('voided_at')->label('Fecha Anulación')
                ->getStateUsing(fn($record) => $record->voided_at?->format('d/m/Y')),
            ExportColumn::make('created_at')->label('Creado')
                ->getStateUsing(fn($record) => $record->created_at?->format('d/m/Y')),
            ExportColumn::make('updated_at')->label('Actualizado')
                ->getStateUsing(fn($record) => $record->updated_at?->format('d/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de documentos fiscales completó con ' . Number::format($export->successful_rows) . ' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas fallaron.';
        }
        return $body;
    }
}
