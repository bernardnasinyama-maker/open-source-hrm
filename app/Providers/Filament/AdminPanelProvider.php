<?php
namespace App\Providers\Filament;

use App\Filament\Pages\AdminDashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Http\Middleware\SetLanguage;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->colors(['primary' => Color::Indigo])
            ->brandName('CRBC Uganda HRM')
            ->renderHook('panels::head.end', fn() => '<link rel="stylesheet" href="/css/login-custom.css">')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([AdminDashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
                SetLanguage::class,
            ])
            ->authGuard('web')
            ->authPasswordBroker('employees')
            ->login()
            ->brandName("CRBC Uganda HRM")
            ->brandLogo(null)
            ->colors(["primary" => [50=>"#f5f3ff",100=>"#ede9fe",200=>"#ddd6fe",300=>"#c4b5fd",400=>"#a78bfa",500=>"#8b5cf6",600=>"#7c3aed",700=>"#6d28d9",800=>"#5b21b6",900=>"#4c1d95",950=>"#2e1065"]])
            ->loginRouteSlug("login")
            ->favicon(null)
            
            ->authMiddleware([
                Authenticate::class,
                'role:super_admin|admin|hr_assistant|viewer',
            ]);
    }
}
