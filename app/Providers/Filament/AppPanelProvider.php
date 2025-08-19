<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Settings;
use App\Filament\Resources\Users\UserResource;
use App\Http\Middleware\CheckVerified;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Enums\ThemeMode;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
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
            ->globalSearch(false)
            ->login(Login::class)
            ->profile(page: EditProfile::class, isSimple: auth()->user()->is_admin ?? false)
            ->multiFactorAuthentication(
                AppAuthentication::make()
                    ->recoverable()
            )
            ->unsavedChangesAlerts()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->colors(fn (): array => config()->array('colors'))
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
            ->userMenuItems([
                'profile' => fn (Action $action): Action => $action->url(fn (): string => EditProfile::getUrl()),
                Action::make('settings')
                    ->icon('tabler-settings')
                    ->label(__('settings.navigation_label'))
                    ->hidden(fn (): bool => ! (auth()->user()->is_admin ?? false))
                    ->url(fn (): string => Settings::getUrl()),
                Action::make('users')
                    ->icon('tabler-users')
                    ->label(__('user.navigation_label'))
                    ->hidden(fn (): bool => ! (auth()->user()->is_admin ?? false))
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
