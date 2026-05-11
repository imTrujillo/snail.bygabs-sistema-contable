<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

/**
 * Igual que Filament\Auth\Notifications\ResetPassword pero sin cola: el correo
 * se envía en la misma petición (Mailtrap/ SMTP sin worker).
 */
class SyncFilamentResetPassword extends ResetPassword
{
    public string $url = '';

    protected function resetUrl($notifiable): string
    {
        return $this->url;
    }
}
