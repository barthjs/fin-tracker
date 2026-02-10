<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Http\Middleware\EnsureUserIsVerified;
use Awcodes\LightSwitch\LightSwitchPlugin;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Enums\Platform;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        if (config('app.allow_registration')) {
            $panel->registration(Register::class);
        }

        $panel
            ->default()
            ->id('app')
            ->path('')
            ->spa()
            ->login(Login::class)
            ->profile(EditProfile::class)
            ->multiFactorAuthentication(
                AppAuthentication::make()->recoverable()
            )
            ->defaultThemeMode(ThemeMode::Dark)
            ->colors(fn (): array => config()->array('colors'))
            ->font('Poppins', provider: LocalFontProvider::class)
            ->viteTheme('resources/css/filament/app/theme.css')
            ->brandLogo(fn (): string => Vite::asset('resources/images/logo/logo-light.svg'))
            ->darkModeBrandLogo(fn (): string => Vite::asset('resources/images/logo/logo-dark.svg'))
            ->brandLogoHeight('3.5rem')
            ->defaultAvatarProvider(LocalAvatarProvider::class)
            ->breadcrumbs(false)
            ->maxContentWidth(Width::Full)
            ->sidebarCollapsibleOnDesktop()
            ->unsavedChangesAlerts(app()->isProduction())
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->globalSearchKeyBindings(['ctrl+k', 'command+k'])
            ->globalSearchFieldSuffix(fn (): ?string => match (Platform::detect()) {
                Platform::Windows, Platform::Linux => (string) __('CTRL+K'),
                Platform::Mac => '⌘K',
                default => null,
            })
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => View::make('components.favicons')->render()
            )
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action->url(fn (): string => EditProfile::getUrl()),
            ])
            ->plugins([
                LightSwitchPlugin::make(),
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
                EnsureUserIsVerified::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        return $panel;
    }
}
