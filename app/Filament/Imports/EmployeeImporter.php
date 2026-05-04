<?php

namespace App\Filament\Imports;

use App\Models\Employee;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmployeeImporter extends Importer
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('position')
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('dui')
                ->rules(['nullable', 'max:20']),
            ImportColumn::make('base_salary')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric', 'min:0']),
            ImportColumn::make('pay_frequency')
                ->requiredMapping()
                ->rules(['required', 'in:Semanal,Quincenal,Mensual']),
            ImportColumn::make('hire_date')
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): Employee
    {
        return new Employee;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de empleados completó con '.Number::format($import->successful_rows).' filas importadas.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' filas fallaron.';
        }

        return $body;
    }
}
