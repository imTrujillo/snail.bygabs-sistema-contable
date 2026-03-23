<?php

namespace App\Filament\Imports;

use App\Models\JournalEntry;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class JournalEntryImporter extends Importer
{
    protected static ?string $model = JournalEntry::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('entry_date')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('description')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('fiscalPeriod')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('user')
                ->requiredMapping()
                ->relationship()
                ->rules(['required']),
            ImportColumn::make('reference_id')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('reference_type')
                ->requiredMapping()
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): JournalEntry
    {
        return new JournalEntry();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your journal entry import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
