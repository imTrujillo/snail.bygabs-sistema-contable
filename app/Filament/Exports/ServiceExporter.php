<?php

namespace App\Filament\Exports;

use App\Models\Service;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ServiceExporter extends Exporter
{
    protected static ?string $model = Service::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Nombre'),
            ExportColumn::make('description')->label('Descripción'),
            ExportColumn::make('price')->label('Precio'),
            ExportColumn::make('duration_minutes')->label('Duración (min)'),
            ExportColumn::make('created_at')->label('Creado')
                ->getStateUsing(fn($record) => $record->created_at?->format('d/m/Y')),
            ExportColumn::make('updated_at')->label('Actualizado')
                ->getStateUsing(fn($record) => $record->updated_at?->format('d/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de servicios completó con ' . Number::format($export->successful_rows) . ' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas fallaron.';
        }
        return $body;
    }
}
