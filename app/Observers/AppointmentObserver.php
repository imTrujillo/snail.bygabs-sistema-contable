<?php

namespace App\Observers;

use App\Models\Appointment;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AppointmentObserver
{
    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        $recipient = Auth::user();
        if (!$recipient) return;

        $date = \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y H:i');

        $customerName = $appointment->customer?->name ?? 'Sin cliente';

        Notification::make()
            ->title('Cita agendada')
            ->body("Cita para **{$customerName}** el {$date}. Estado: *{$appointment->status->value}*.")
            ->success()
            ->sendToDatabase($recipient);
    }
}
