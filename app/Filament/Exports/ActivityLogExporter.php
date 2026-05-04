<?php

namespace App\Filament\Exports;

use App\Models\ActivityLog;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ActivityLogExporter extends Exporter
{
    protected static ?string $model = ActivityLog::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('log_name')->label('Módulo'),
            ExportColumn::make('description')->label('Acción'),
            ExportColumn::make('subject_type')->label('Modelo'),
            ExportColumn::make('event')->label('Evento'),
            ExportColumn::make('causer.name')->label('Usuario'),
            ExportColumn::make('created_at')->label('Fecha')
                ->getStateUsing(fn ($record) => $record->created_at?->format('d/m/Y H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de actividad completó con '.Number::format($export->successful_rows).' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' filas fallaron.';
        }

        return $body;
    }
}
