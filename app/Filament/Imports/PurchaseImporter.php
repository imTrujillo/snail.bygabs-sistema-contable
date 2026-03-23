<?php

namespace App\Filament\Imports;

use App\Models\Purchase;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class PurchaseImporter extends Importer
{
    protected static ?string $model = Purchase::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('supplier')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('taxDocument')
                ->relationship(),
            ImportColumn::make('purchase_date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('exempt_amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('non_taxable_amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('taxable_amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('credit_fiscal')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('total_amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('account')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('notes')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
        ];
    }

    public function resolveRecord(): Purchase
    {
        return new Purchase();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your purchase import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
