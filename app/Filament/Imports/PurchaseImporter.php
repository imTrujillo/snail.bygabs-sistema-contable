<?php

namespace App\Filament\Imports;

use App\Models\Account;
use App\Models\Purchase;
use App\Models\Supplier;
use Carbon\Carbon;
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

            ImportColumn::make('taxDocument')
                ->label('Documento Fiscal')
                ->relationship(),
            ImportColumn::make('purchase_date')
                ->label('Fecha')
                ->example('23/03/2026')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (string $state): ?string {
                    if (blank($state)) return null;
                    return Carbon::createFromFormat('d/m/Y', $state)->format('Y-m-d');
                }),
            ImportColumn::make('exempt_amount')
                ->label('Monto Exento')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('non_taxable_amount')
                ->label('Monto No Gravado')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('taxable_amount')
                ->label('Monto Gravado')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('credit_fiscal')
                ->label('Crédito Fiscal')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('total_amount')
                ->label('Total')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('supplier')
                ->label('Proveedor')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Purchase $record, string $state): void {
                    $supplier = Supplier::where('name', $state)->first();
                    $record->supplier_id = $supplier?->id;
                }),
            ImportColumn::make('account')
                ->label('Cuenta')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Purchase $record, string $state): void {
                    $account = Account::where('name', $state)->first();
                    $record->account_id = $account?->id;
                }),
            ImportColumn::make('notes')
                ->label('Notas')
                ->rules(['nullable', 'max:255']),
        ];
    }

    public function resolveRecord(): Purchase
    {
        return new Purchase();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de compras completó con ' . Number::format($import->successful_rows) . ' ' . str('fila')->plural($import->successful_rows) . ' importadas.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' fallaron.';
        }
        return $body;
    }
}
