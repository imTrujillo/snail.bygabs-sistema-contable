<?php

namespace App\Filament\Imports;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
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
                ->label('Cliente')
                ->example('Mark Patel')
                ->requiredMapping()
                ->rules(['required']),

            ImportColumn::make('user')
                ->label('Responsable')
                ->example('Administrador')
                ->requiredMapping()
                ->rules(['required']),

            ImportColumn::make('appointment_date')
                ->label('Fecha')
                ->example('23/03/2026 16:27')
                ->requiredMapping()
                ->rules(['required', 'datetime']),

            ImportColumn::make('status')
                ->label('Estado')
                ->example('Pendiente')
                ->requiredMapping()
                ->rules(['required']),

            ImportColumn::make('notes')
                ->label('Notas')
                ->example('Nota de ejemplo')
                ->rules(['nullable', 'max:255']),
        ];
    }

    public function resolveRecord(): Appointment
    {
        $data = $this->data;

        $customer = Customer::where('name', $data['customer'])->first();
        $user = User::where('name', $data['user'])->first();

        // Parsear fecha d/m/Y H:i antes de asignar al modelo
        $date = Carbon::createFromFormat('d/m/Y H:i', $data['appointment_date']);

        $appointment = new Appointment();
        $appointment->customer_id      = $customer?->id;
        $appointment->user_id          = $user?->id;
        $appointment->appointment_date = $date; // ← Carbon object, no string
        $appointment->status           = $data['status'];
        $appointment->notes            = $data['notes'] ?? null;

        return $appointment;
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
