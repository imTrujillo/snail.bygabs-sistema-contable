<?php

namespace App\Filament\Exports;

use App\Models\Appointment;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class AppointmentExporter extends Exporter
{
    protected static ?string $model = Appointment::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),

            ExportColumn::make('customer.name')
                ->label('Cliente'),

            ExportColumn::make('user.name')
                ->label('Responsable'),

            ExportColumn::make('appointment_date')
                ->label('Fecha')
                ->getStateUsing(fn($record) => $record->appointment_date?->format('d/m/Y H:i')),

            ExportColumn::make('status')
                ->label('Estado')
                ->getStateUsing(fn($record) => $record->status?->value ?? ''),

            ExportColumn::make('notes')
                ->label('Notas'),

            ExportColumn::make('created_at')
                ->label('Creado')
                ->getStateUsing(fn($record) => $record->created_at?->format('d/m/Y')),

            ExportColumn::make('updated_at')
                ->label('Actualizado')
                ->getStateUsing(fn($record) => $record->updated_at?->format('d/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your appointment export has completed and ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
