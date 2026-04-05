<?php

namespace App\Filament\Exports;

use App\Models\JournalEntry;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class JournalEntryExporter extends Exporter
{
    protected static ?string $model = JournalEntry::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('entry_date')->label('Fecha')
                ->getStateUsing(fn($record) => $record->entry_date?->format('d/m/Y')),
            ExportColumn::make('description')->label('Descripción'),
            ExportColumn::make('fiscalPeriod.name')->label('Período Fiscal'),
            ExportColumn::make('user.name')->label('Usuario'),
            ExportColumn::make('reference_id')->label('ID Referencia'),
            ExportColumn::make('reference_type')->label('Tipo Referencia'),
            ExportColumn::make('created_at')->label('Creado')
                ->getStateUsing(fn($record) => $record->created_at?->format('d/m/Y')),
            ExportColumn::make('updated_at')->label('Actualizado')
                ->getStateUsing(fn($record) => $record->updated_at?->format('d/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de asientos contables completó con ' . Number::format($export->successful_rows) . ' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas fallaron.';
        }
        return $body;
    }
}
