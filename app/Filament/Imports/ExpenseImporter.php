<?php

namespace App\Filament\Imports;

use App\Models\Account;
use App\Models\Expense;
use Carbon\Carbon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ExpenseImporter extends Importer
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('description')
                ->label('Descripción')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('category')
                ->label('Categoría')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('amount')
                ->label('Monto')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'numeric']),
            ImportColumn::make('expense_date')
                ->label('Fecha')
                ->example('23/03/2026')
                ->requiredMapping()
                ->rules(['required'])
                ->castStateUsing(function (string $state): ?string {
                    if (blank($state)) return null;
                    return Carbon::createFromFormat('d/m/Y', $state)->format('Y-m-d');
                }),
            ImportColumn::make('paid_with')
                ->label('Pagado con')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('account')
                ->label('Cuenta')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (Expense $record, string $state): void {
                    $account = Account::where('name', $state)->first();
                    $record->account_id = $account?->id;
                }),
            ImportColumn::make('notes')
                ->label('Notas')
                ->rules(['nullable', 'max:255']),
        ];
    }

    public function resolveRecord(): Expense
    {
        return new Expense();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'La importación de gastos completó con ' . Number::format($import->successful_rows) . ' ' . str('fila')->plural($import->successful_rows) . ' importadas.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('fila')->plural($failedRowsCount) . ' fallaron.';
        }
        return $body;
    }
}
