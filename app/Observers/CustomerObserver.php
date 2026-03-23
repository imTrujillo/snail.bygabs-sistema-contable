<?php

namespace App\Observers;

use App\Models\Customer;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CustomerObserver
{
    /**
     * Handle the Customer "created" event.
     */
    public function created(Customer $customer): void
    {
        $recipient = Auth::user();
        if (!$recipient) return;

        Notification::make()
            ->title('Cliente registrado')
            ->body("**{$customer->name}** fue agregado. Teléfono: {$customer->phone}.")
            ->success()
            ->sendToDatabase($recipient);
    }
}
