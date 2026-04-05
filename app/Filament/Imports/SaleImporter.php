<?php

namespace App\Filament\Imports;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\TaxDocument;
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
            ImportColumn::make('customer_name')
                ->label('Cliente')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Sale $record, string $state): void {
                    $customer = Customer::where('name', $state)->first();
                    $record->customer_id = $customer?->id;
                }),

            ImportColumn::make('appointment')
                ->label('Cita')
                ->fillRecordUsing(function (Sale $record, string $state): void {
                    $record->appointment_id = is_numeric($state) ? (int) $state : null;
                }),

            ImportColumn::make('taxDocument')
                ->label('Doc. Fiscal')
                ->fillRecordUsing(function (Sale $record, string $state): void {
                    $record->tax_document_id = is_numeric($state) ? (int) $state : null;
                }),

            ImportColumn::make('total')
                ->label('Total')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),

            ImportColumn::make('payment_method')
                ->label('Método de Pago')
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
        $body = 'La importación de ventas completó con ' . Number::format($import->successful_rows) . ' filas importadas.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' filas fallaron.';
        }
        return $body;
    }
}
