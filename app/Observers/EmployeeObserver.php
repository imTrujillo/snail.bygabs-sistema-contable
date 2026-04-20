<?php

namespace App\Observers;

use App\Models\Employee;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        $recipient = Auth::user();
        if (!$recipient) return;

        Notification::make()
            ->title('Empleado creado')
            ->body("**{$employee->name}** — Cargo: {$employee->position}.")
            ->info()
            ->sendToDatabase($recipient);
    }

    /**
     * Handle the Employee "updated" event.
     */
    public function updated(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "deleted" event.
     */
    public function deleted(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "restored" event.
     */
    public function restored(Employee $employee): void
    {
        //
    }

    /**
     * Handle the Employee "force deleted" event.
     */
    public function forceDeleted(Employee $employee): void
    {
        //
    }
}
