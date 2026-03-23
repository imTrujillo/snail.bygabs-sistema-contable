<?php

namespace App\Filament\Imports;

use App\Models\Appointment;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class AppointmentImporter extends Importer
{
    protected static ?string $model = Appointment::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('customer')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('user')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('appointment_date')
                ->requiredMapping()
                ->rules(['required', 'datetime']),
            ImportColumn::make('status')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('notes')
                ->rules(['max:255']),
        ];
    }

    public function resolveRecord(): Appointment
    {
        return new Appointment();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your appointment import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
