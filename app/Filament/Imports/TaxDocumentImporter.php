<?php

namespace App\Filament\Imports;

use App\Models\TaxDocument;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class TaxDocumentImporter extends Importer
{
    protected static ?string $model = TaxDocument::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('type')
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('series')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('correlative_number')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('document_number')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('issue_date')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('customer')
                ->relationship(),
            ImportColumn::make('supplier')
                ->relationship(),
            ImportColumn::make('reference_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('reference_type')
                ->requiredMapping()
                ->rules(['required']),
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
            ImportColumn::make('iva_amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('total_amount')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('is_voided')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('voided_at')
                ->rules(['datetime']),
        ];
    }

    public function resolveRecord(): TaxDocument
    {
        return new TaxDocument();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your tax document import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
