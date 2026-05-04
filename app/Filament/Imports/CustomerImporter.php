<?php

namespace App\Filament\Imports;

use App\Models\Customer;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Illuminate\Validation\Rule;

class CustomerImporter extends Importer
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Nombre')
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255']),
            ImportColumn::make('phone')
                ->label('Teléfono')
                ->requiredMapping()
                ->rules(['required', 'string', 'regex:/^[67]\d{7}$/']),
            ImportColumn::make('email')
                ->label('Correo')
                ->rules(['nullable', 'email', 'max:255']),
            ImportColumn::make('notes')
                ->label('Notas')
                ->rules(['nullable', 'string', 'max:500']),
            ImportColumn::make('is_contributor')
                ->label('¿Contribuyente? (SI/NO, 1/0)')
                ->boolean()
                ->rules(['sometimes', 'boolean']),
            ImportColumn::make('nrc')
                ->label('NRC')
                ->rules(['sometimes', 'nullable', 'regex:/^\d{1,6}-\d$/', 'max:20']),
            ImportColumn::make('nit')
                ->label('NIT')
                ->rules(['sometimes', 'nullable', 'regex:/^\d{4}-\d{6}-\d{3}-\d$/', 'max:17']),
        ];
    }

    protected function beforeValidate(): void
    {
        $raw = $this->data['is_contributor'] ?? false;
        $converted = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($converted !== null) {
            $this->data['is_contributor'] = $converted;
        } else {
            $s = strtolower(trim((string) $raw));
            $this->data['is_contributor'] = in_array($s, ['si', 'sí', 's', 'yes', 'y', '1', 'true', 'contribuyente'], true);
        }

        foreach (['nit', 'nrc', 'notes', 'email'] as $field) {
            $v = $this->data[$field] ?? null;
            $this->data[$field] = filled($v) ? (is_string($v) ? trim($v) : $v) : null;
        }
    }

    public function validateData(): void
    {
        parent::validateData();

        if ($this->data['is_contributor'] ?? false) {
            Validator::make(
                ['nrc' => $this->data['nrc'] ?? null, 'nit' => $this->data['nit'] ?? null],
                [
                    'nrc' => ['required', 'regex:/^\d{1,6}-\d$/', Rule::unique('customers', 'nrc')],
                    'nit' => ['required', 'regex:/^\d{4}-\d{6}-\d{3}-\d$/', Rule::unique('customers', 'nit')],
                ],
                [],
                ['nrc' => 'NRC', 'nit' => 'NIT']
            )->validate();
        }

        $email = $this->data['email'] ?? null;
        if (filled($email)) {
            Validator::make(
                ['email' => $email],
                ['email' => ['email', 'max:255', Rule::unique('customers', 'email')]],
                [],
                ['email' => 'correo']
            )->validate();
        }
    }

    public function resolveRecord(): Customer
    {
        return new Customer;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Importación de clientes finalizada: '.Number::format($import->successful_rows).' '.'fila'.($import->successful_rows !== 1 ? 's' : '').' importada'.($import->successful_rows !== 1 ? 's' : '').'.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.'fila'.($failedRowsCount !== 1 ? 's' : '').' con error.';
        }

        return $body;
    }
}
