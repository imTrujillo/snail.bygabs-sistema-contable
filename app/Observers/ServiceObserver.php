<?php

namespace App\Observers;

use App\Models\Service;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ServiceObserver
{
    /**
     * Handle the Service "created" event.
     */
    public function created(Service $service): void
    {
        $recipient = Auth::user();
        if (!$recipient) return;

        Notification::make()
            ->title('Servicio creado')
            ->body("**{$service->name}** agregado con precio de \${$service->price} y duración de {$service->duration_minutes} minutos.")
            ->success()
            ->sendToDatabase($recipient);
    }
}
