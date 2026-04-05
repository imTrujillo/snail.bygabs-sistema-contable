<?php

namespace App\Filament\Exports;

use App\Models\Sale;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class SaleExporter extends Exporter
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('customer.name')->label('customer_name'),
            ExportColumn::make('appointment.id')->label('Cita'),
            ExportColumn::make('taxDocument.id')->label('Doc. Fiscal'),
            ExportColumn::make('total')->label('Total'),
            ExportColumn::make('payment_method')->label('Método de Pago'),
            ExportColumn::make('created_at')->label('Creado')
                ->getStateUsing(fn($record) => $record->created_at?->format('d/m/Y')),
            ExportColumn::make('updated_at')->label('Actualizado')
                ->getStateUsing(fn($record) => $record->updated_at?->format('d/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de ventas completó con ' . Number::format($export->successful_rows) . ' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas fallaron.';
        }
        return $body;
    }
}
