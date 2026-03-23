<?php

namespace App\Observers;

use App\Models\Supplier;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class SupplierObserver
{
    /**
     * Handle the Supplier "created" event.
     */
    public function created(Supplier $supplier): void
    {
        $recipient = Auth::user();
        if (!$recipient) return;

        Notification::make()
            ->title('Proveedor registrado')
            ->body("**{$supplier->name}** agregado. NRC: {$supplier->nrc} — NIT: {$supplier->nit}.")
            ->success()
            ->sendToDatabase($recipient);
    }
}
