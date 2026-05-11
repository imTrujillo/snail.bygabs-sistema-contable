<?php

namespace App\Filament\Auth;

use Filament\Actions\Action;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Facades\Filament;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;

/**
 * Igual que la página de Filament, pero permite usuarios autenticados
 * (p. ej. desde "Mi perfil") y precarga el correo.
 */
class RequestPasswordLink extends RequestPasswordReset
{
    public function mount(): void
    {
        $fill = [];
        if (Filament::auth()->check()) {
            $fill['email'] = Filament::auth()->user()->getAttribute('email');
        }

        $this->form->fill($fill);
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Correo electrónico')
            ->disabled(fn (): bool => Filament::auth()->check())
            ->dehydrated(true);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Recuperar contraseña';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Recuperar contraseña';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (Filament::auth()->check()) {
            return Action::make('backToPanel')
                ->link()
                ->label('Volver al panel')
                ->url(Filament::getUrl());
        }

        return parent::getSubheading();
    }

    protected function getRequestFormAction(): Action
    {
        return parent::getRequestFormAction()
            ->label('Enviar enlace al correo');
    }
}
