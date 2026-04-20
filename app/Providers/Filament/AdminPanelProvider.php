<?php

namespace App\Providers\Filament;

use App\Filament\Pages\SelectFiscalPeriod;
use App\Filament\Widgets\AppointmentWidget;
use App\Filament\Widgets\CalendarWidget;
use App\Filament\Widgets\InvoiceWidget;
use App\Filament\Widgets\SaleWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Http\Middleware\EnsureActiveFiscalPeriod;
use App\Models\CompanySetting;
use DiogoGPinto\AuthUIEnhancer\AuthUIEnhancerPlugin;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => [
                    50  => '#faf6ee',
                    100 => '#f5efdb',
                    200 => '#ede3c4',
                    300 => '#d9ccaa',
                    400 => '#c0aa80',
                    500 => '#9a7c46',
                    600 => '#6b5527',
                    700 => '#473919',
                    800 => '#352a12',
                    900 => '#241c0c',
                    950 => '#140f05',
                ],
            ])
            ->font('Cinzel', provider: GoogleFontProvider::class)
            ->brandLogo(
                fn() =>
                CompanySetting::current()->logo && file_exists(public_path('storage/' . CompanySetting::current()->logo))
                    ? asset('storage/' . CompanySetting::current()->logo)
                    : asset('/logo.jpeg')
            )
            ->favicon(
                fn() =>
                CompanySetting::current()->logo && file_exists(public_path('storage/' . CompanySetting::current()->logo))
                    ? asset('storage/' . CompanySetting::current()->logo)
                    : asset('/logo.png')
            )
            ->brandLogoHeight('3rem')
            ->brandName(fn() => CompanySetting::current()?->name ?? config('app.name'))
            ->darkMode(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                SelectFiscalPeriod::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                StatsOverviewWidget::class,
                InvoiceWidget::class,
                AppointmentWidget::class,
                SaleWidget::class,
                CalendarWidget::class
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                EnsureActiveFiscalPeriod::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->databaseNotifications()
            ->navigationGroups([
                'Operativo',
                'Fiscal',
                'Contabilidad',
                'Reportes',
                'Configuración'
            ])
            ->plugins([
                AuthUIEnhancerPlugin::make()
            ])
            ->renderHook(
                'panels::body.start',
                fn() => request()->routeIs('filament.admin.auth.login') ? new HtmlString('
                    <div id="caracol-branding-left">
                        <img src="/logo.jpeg" alt="Caracol Studio Logo">
                    </div>
                    <style>
                        /* Ocultar logos por defecto de Laravel en Login */
                        .fi-simple-layout img[alt*="Laravel"], .fi-logo { display: none !important; }

                        #caracol-branding-left {
                            position: fixed;
                            left: 0;
                            top: 0;
                            width: 50%;
                            height: 100vh;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            z-index: 50;
                            pointer-events: none;
                        }

                        #caracol-branding-left img {
                            width: 380px; /* Tamaño profesional */
                            height: auto;
                            filter: drop-shadow(0 10px 15px rgba(0,0,0,0.1));
                            opacity: 0.95;
                        }

                        @media (max-width: 1024px) {
                            #caracol-branding-left img {
                                display: none;
                            }
                        }

                        .caracol-auth-header {
                            width: 100%;
                            text-align: center;
                            margin-bottom: 2rem;
                        }

                        .caracol-auth-header h1 {
                            font-family: "Cinzel", serif;
                            font-size: 3rem;
                            color: #473919;
                            font-weight: bold;
                            text-transform: lowercase;
                            letter-spacing: 2px;
                        }
                    </style>
                ') : null
            )
            ->renderHook(
                'panels::auth.login.form.before',
                fn() => new HtmlString('
                    <div class="caracol-auth-header">
                        <h1>caracol studio</h1>
                    </div>
                ')
            )
            ->viteTheme('resources/css/filament/admin/theme.css')
        ;
    }
}
