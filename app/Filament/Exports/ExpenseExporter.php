<?php

namespace App\Filament\Exports;

use App\Models\Expense;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class ExpenseExporter extends Exporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('ID'),
            ExportColumn::make('description')->label('Descripción'),
            ExportColumn::make('category')->label('Categoría'),
            ExportColumn::make('amount')->label('Monto'),
            ExportColumn::make('expense_date')->label('Fecha')
                ->getStateUsing(fn($record) => $record->expense_date?->format('d/m/Y')),
            ExportColumn::make('paid_with')->label('Pagado con'),
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
        $body = 'La exportación de gastos completó con ' . Number::format($export->successful_rows) . ' filas exportadas.';
        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas fallaron.';
        }
        return $body;
    }
}
