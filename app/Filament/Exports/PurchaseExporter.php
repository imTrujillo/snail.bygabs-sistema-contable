<?php

namespace App\Filament\Exports;

use App\Models\Purchase;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PurchaseExporter extends Exporter
{
    protected static ?string $model = Purchase::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('supplier.name')->label('Proveedor'),
            ExportColumn::make('taxDocument.id')->label('Doc. Fiscal'),
            ExportColumn::make('purchase_date')->label('Fecha')
                ->getStateUsing(fn($record) => $record->purchase_date?->format('d/m/Y')),
            ExportColumn::make('exempt_amount')->label('Monto Exento'),
            ExportColumn::make('non_taxable_amount')->label('Monto No Gravado'),
            ExportColumn::make('taxable_amount')->label('Monto Gravado'),
            ExportColumn::make('credit_fiscal')->label('Crédito Fiscal'),
            ExportColumn::make('total_amount')->label('Total'),
            ExportColumn::make('account.name')->label('Cuenta'),
            ExportColumn::make('notes')->label('Notas'),
            ExportColumn::make('created_at')->label('Creado')
                ->getStateUsing(fn($record) => $record->created_at?->format('d/m/Y')),
            ExportColumn::make('updated_at')->label('Actualizado')
                ->getStateUsing(fn($record) => $record->updated_at?->format('d/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de compras completó con ' . Number::format($export->successful_rows) . ' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas fallaron.';
        }
        return $body;
    }
}
