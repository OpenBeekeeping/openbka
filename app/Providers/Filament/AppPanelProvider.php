<?php

namespace App\Providers\Filament;

use App\Billing\StripeBillingProvider;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Tenancy\EditAssociation;
use App\Filament\Pages\Tenancy\RegisterAssociation;
use App\Models\Association;
use App\Support\Design;
use App\Support\Mode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\View\View;
use MarcelWeidum\Passkeys\PasskeysPlugin;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $hosted = Mode::hosted();

        return $panel
            ->default()
            ->id('app')
            ->path('app')
            ->brandName('OpenBKA')
            ->login(Login::class)
            ->registration($hosted ? Register::class : null)
            ->profile(isSimple: false)
            ->tenant(Association::class, slugAttribute: 'slug')
            ->tenantProfile(EditAssociation::class)
            ->tenantRegistration($hosted ? RegisterAssociation::class : null)
            ->tenantBillingProvider($hosted ? new StripeBillingProvider : null)
            ->requiresTenantSubscription($hosted)
            // Bickerton Beekeeping design system (light-only), see openbka:sync-design.
            ->darkMode(false)
            ->font('DM Sans Variable', url: asset('bickerton/css/fonts.css'), provider: LocalFontProvider::class)
            ->colors(Design::colors())
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): View => view('filament.head'))
            ->brandLogo(fn (): View => view('filament.brand'))
            ->brandLogoHeight('2.25rem')
            ->favicon(asset('bickerton/brand/favicon.svg'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->plugins([
                PasskeysPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
