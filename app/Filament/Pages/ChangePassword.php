<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Pages\Page;

/**
 * Ruta legada: el flujo real de contraseña es por correo (recuperación SMTP).
 */
class ChangePassword extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament-panels::pages.page';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function mount(): void
    {
        $url = Filament::getRequestPasswordResetUrl();

        if ($url) {
            $this->redirect($url);
        } else {
            $this->redirect(Filament::getUrl());
        }
    }
}
