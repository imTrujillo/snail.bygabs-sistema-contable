<?php

namespace App\Filament\Exports;

use App\Models\Payroll;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class PayrollExporter extends Exporter
{
    protected static ?string $model = Payroll::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('pay_date')->label('Fecha de pago')
                ->getStateUsing(fn ($record) => $record->pay_date?->format('d/m/Y')),
            ExportColumn::make('period_type')->label('Tipo'),
            ExportColumn::make('fiscalPeriod.name')->label('Período fiscal'),
            ExportColumn::make('total_gross')->label('Total bruto'),
            ExportColumn::make('total_net')->label('Total neto'),
            ExportColumn::make('user.name')->label('Registrado por'),
            ExportColumn::make('created_at')->label('Creado')
                ->getStateUsing(fn ($record) => $record->created_at?->format('d/m/Y H:i')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'La exportación de planillas completó con '.Number::format($export->successful_rows).' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' filas fallaron.';
        }

        return $body;
    }
}
