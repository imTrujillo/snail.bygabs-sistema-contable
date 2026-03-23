<?php

namespace App\Observers;

use App\Models\TaxDocument;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class TaxDocumentObserver
{
    /**
     * Handle the TaxDocument "created" event.
     */
    public function created(TaxDocument $taxDocument): void
    {
        $recipient = Auth::user();
        if (!$recipient) return;

        $tipo = $taxDocument->reference_type === 'sale' ? 'Venta' : 'Compra';

        Notification::make()
            ->title('Documento fiscal generado')
            ->body("**{$taxDocument->document_number}** ({$taxDocument->type}) — {$tipo}. Total: \${$taxDocument->total_amount}, IVA: \${$taxDocument->iva_amount}.")
            ->success()
            ->sendToDatabase($recipient);
    }
}
