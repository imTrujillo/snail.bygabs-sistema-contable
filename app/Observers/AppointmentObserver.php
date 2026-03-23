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

        Notification::make()
            ->title('Cita agendada')
            ->body("Cita para **{$appointment->client->name}** el {$date}. Estado: *{$appointment->status}*.")
            ->success()
            ->sendToDatabase($recipient);
    }
}
