<?php

namespace App\Filament\Exports;

use App\Models\Product;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ProductExporter extends Exporter
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('unit')->label('Unidad'),
            ExportColumn::make('name')->label('Nombre'),
            ExportColumn::make('stock')->label('Stock'),
            ExportColumn::make('cost_price')->label('Precio Costo'),
            ExportColumn::make('sale_price')->label('Precio Venta'),
            ExportColumn::make('created_at')->label('Creado')
                ->getStateUsing(fn($record) => $record->created_at?->format('d/m/Y')),
            ExportColumn::make('updated_at')->label('Actualizado')
                ->getStateUsing(fn($record) => $record->updated_at?->format('d/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de productos completó con ' . Number::format($export->successful_rows) . ' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas fallaron.';
        }
        return $body;
    }
}
