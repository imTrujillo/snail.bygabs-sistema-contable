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
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('type'),
            ExportColumn::make('series'),
            ExportColumn::make('correlative_number'),
            ExportColumn::make('document_number'),
            ExportColumn::make('issue_date'),
            ExportColumn::make('customer.name'),
            ExportColumn::make('supplier.name'),
            ExportColumn::make('reference_id'),
            ExportColumn::make('reference_type'),
            ExportColumn::make('exempt_amount'),
            ExportColumn::make('non_taxable_amount'),
            ExportColumn::make('taxable_amount'),
            ExportColumn::make('iva_amount'),
            ExportColumn::make('total_amount'),
            ExportColumn::make('is_voided'),
            ExportColumn::make('voided_at'),
            ExportColumn::make('created_at'),
            ExportColumn::make('updated_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your tax document export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
