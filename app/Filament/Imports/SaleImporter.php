<?php

namespace App\Filament\Imports;

use App\Models\Sale;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class SaleImporter extends Importer
{
    protected static ?string $model = Sale::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('customer')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('appointment')
                ->relationship(),
            ImportColumn::make('taxDocument')
                ->relationship(),
            ImportColumn::make('total')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('payment_method')
                ->requiredMapping()
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): Sale
    {
        return new Sale();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your sale import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
