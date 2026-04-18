<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\FiscalPeriod;
use App\Models\Sale;
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

    public function updated(Appointment $appointment): void
    {
        if (!$appointment->wasChanged('status')) return;
        if ($appointment->status->value !== 'Completada') return;
        if ($appointment->sale()->exists()) return;

        $this->createSaleIfCompleted($appointment);
    }

    protected function createSaleIfCompleted(Appointment $appointment): void
    {
        $period = FiscalPeriod::find(session('active_fiscal_period_id'));

        if (!$period) {
            Notification::make()
                ->title('Sin período fiscal activo')
                ->body('No se pudo registrar la venta automáticamente.')
                ->warning()
                ->sendToDatabase(Auth::user());
            return;
        }

        $total = $appointment->appointmentServices()->sum('price');
        $sale = Sale::create([
            'customer_id'    => $appointment->customer_id,
            'appointment_id' => $appointment->id,
            'total'          => $total,
            'payment_method' => 'Efectivo',
            'document_type'  => $appointment->customer->is_contributor ? 'CCF' : 'FCF',
        ]);

        $recipient = Auth::user();
        if ($recipient) {
            Notification::make()
                ->title('Venta generada automáticamente')
                ->body("Cita completada → Venta #{$sale->id} por \${$total}.")
                ->success()
                ->sendToDatabase($recipient);
        }
    }
}
