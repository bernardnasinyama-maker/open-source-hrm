<?php

namespace App\Providers\Filament;

use App\Filament\Employee\Pages\Dashboard;
use App\Filament\Pages\TaskBoard;

use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use App\Http\Middleware\SetLanguage;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Resources\Messages\MessageResource;
use App\Filament\Widgets\AllCalendarWidget;

class EmployeePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('hr')->authGuard('web')
            ->path('hr')
            ->colors([
                'primary' => Color::Cyan,
            ])
            ->databaseNotifications()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->spa()
            ->login()
            ->passwordReset()
            ->profile()
            ->brandName('CRBC Uganda · HR Portal')
            ->renderHook('panels::head.end', fn() => '<link rel="stylesheet" href="/css/login-custom.css">')  // or 'CRBC HRM · HR Portal'
              ->renderHook(
                  'panels::body.end',
                  fn() => view('filament.branding-footer')
             )
            ->discoverResources(in: app_path('Filament/Employee/Resources'), for: 'App\\Filament\\Employee\\Resources')
            ->discoverPages(in: app_path('Filament/Employee/Pages'), for: 'App\\Filament\\Employee\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Employee/Widgets'), for: 'App\\Filament\\Employee\\Widgets')
            ->widgets([
                // Add any HR-specific widgets here if needed
            ])
            ->navigationGroups([
                'HR Management',
                'Project',
                'Finance',
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
                SetLanguage::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}