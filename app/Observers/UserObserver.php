<?php

namespace App\Observers;

use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $recipient = Auth::user();
        if (!$recipient) return;

        Notification::make()
            ->title('Usuario creado')
            ->body("**{$user->name}** fue registrado con el rol de *{$user->role}*.")
            ->success()
            ->sendToDatabase($recipient);
    }
}
