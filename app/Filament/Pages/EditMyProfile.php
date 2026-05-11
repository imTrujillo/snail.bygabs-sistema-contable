<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * Datos personales en el panel. La contraseña se cambia por enlace al correo (SMTP).
 */
class EditMyProfile extends BaseEditProfile
{
    protected static ?string $title = 'Mi perfil';

    public static function getLabel(): string
    {
        return 'Mi perfil';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Mi perfil';
    }

    public function form(Schema $schema): Schema
    {
        $this->defaultForm($schema);

        $resetUrl = Filament::getRequestPasswordResetUrl() ?? '#';

        return $schema->components([
            Section::make('Datos personales')
                ->icon('heroicon-o-user')
                ->schema([
                    $this->getNameFormComponent(),
                    $this->getEmailFormComponent(),
                    $this->getCurrentPasswordFormComponent(),
                ]),

            Section::make('Contraseña')
                ->icon('heroicon-o-lock-closed')
                ->description(
                    'Recibirás un enlace por correo (Mailtrap).'
                )
                ->schema([
                    Placeholder::make('password_reset_hint')
                        ->hiddenLabel()
                        ->content(new HtmlString(
                            '<p class="mt-1 text-sm text-gray-600 dark:text-gray-400">'
                            .'El enlace te permite definir una contraseña nueva; el buzón del usuario puede ser de cualquier dominio.'
                            .'</p>'
                            .'<p class="mt-3">'
                            .'<a href="'.e($resetUrl).'" class="font-medium text-primary-600 underline decoration-2 underline-offset-2 hover:text-primary-700">'
                            .'Solicitar enlace para nueva contraseña'
                            .'</a>'
                            .'</p>'
                        )),
                ]),
        ]);
    }

    protected function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent()
            ->label('Nombre completo');
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->label('Correo electrónico');
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return parent::getCurrentPasswordFormComponent()
            ->label('Contraseña actual')
            ->belowContent('Solo si modificas el correo electrónico.');
    }
}
