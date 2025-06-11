<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Settings;
use App\Filament\Resources\UserResource;
use App\Http\Middleware\CheckVerified;
use App\Livewire\CustomPersonalInfo;
use App\Livewire\CustomTwoFactorAuthentication;
use App\Livewire\CustomUpdatePassword;
use App\Tools\LocalAvatarProvider;
use Exception;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\App;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use Leandrocfe\FilamentApexCharts\FilamentApexChartsPlugin;

class AppPanelProvider extends PanelProvider
{
    /**
     * @throws Exception
     */
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
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->colors(config('colors'))
            ->font('Poppins',
                '/fonts/fonts.css',
                LocalFontProvider::class
            )
            ->defaultAvatarProvider(LocalAvatarProvider::class)
            ->viteTheme('resources/css/filament/app/theme.css')
            ->defaultThemeMode(ThemeMode::Dark)
            ->maxContentWidth('full')
            ->readOnlyRelationManagersOnResourceViewPagesByDefault(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        hasAvatars: true,
                        slug: __('user.profile-slug'),
                    )
                    ->customMyProfilePage(EditProfile::class)
                    ->myProfileComponents([
                        'personal_info' => CustomPersonalInfo::class,
                        'update_password' => CustomUpdatePassword::class,
                        'two_factor_authentication' => CustomTwoFactorAuthentication::class,
                    ])
                    ->enableTwoFactorAuthentication(),
                FilamentApexChartsPlugin::make(),
            ])
            ->userMenuItems([
                'settings' => MenuItem::make()
                    ->label(__('settings.navigation_label'))
                    ->icon('tabler-settings')
                    ->hidden(fn () => App::runningInConsole() || ! auth()->user()->is_admin)
                    ->url(fn (): string => Settings::getUrl()),
                'users' => MenuItem::make()
                    ->label(__('user.navigation_label'))
                    ->icon('tabler-users')
                    ->hidden(fn () => App::runningInConsole() || ! auth()->user()->is_admin)
                    ->url(fn (): string => UserResource::getUrl()),
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
                CheckVerified::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        return $panel;
    }
}
