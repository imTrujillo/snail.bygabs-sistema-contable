<?php

namespace App\Observers;

use App\Models\JournalEntry;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class JournalEntryObserver
{
    /**
     * Handle the JournalEntry "created" event.
     */
    public function created(JournalEntry $journalEntry): void
    {
        $recipient = Auth::user();
        if (!$recipient) return;

        Notification::make()
            ->title('Asiento contable creado')
            ->body("**{$journalEntry->description}** — Periodo: *{$journalEntry->fiscalPeriod?->name}*. Fecha: {$journalEntry->entry_date->format('d/m/Y')}.")
            ->info()
            ->sendToDatabase($recipient);
    }
}
