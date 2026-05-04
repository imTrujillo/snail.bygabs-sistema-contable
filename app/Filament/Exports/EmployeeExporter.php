<?php

namespace App\Filament\Exports;

use App\Models\Employee;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class EmployeeExporter extends Exporter
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('name')->label('Nombre'),
            ExportColumn::make('position')->label('Cargo'),
            ExportColumn::make('dui')->label('DUI'),
            ExportColumn::make('base_salary')->label('Salario base'),
            ExportColumn::make('pay_frequency')->label('Frecuencia'),
            ExportColumn::make('is_active')->label('Activo'),
            ExportColumn::make('hire_date')->label('Contratado')
                ->getStateUsing(fn ($record) => $record->hire_date?->format('d/m/Y')),
            ExportColumn::make('created_at')->label('Creado')
                ->getStateUsing(fn ($record) => $record->created_at?->format('d/m/Y')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de empleados completó con '.Number::format($export->successful_rows).' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' filas fallaron.';
        }

        return $body;
    }
}
