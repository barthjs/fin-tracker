<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use App\Services\Oidc\OidcService;
use Auth;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Log;
use Throwable;

final readonly class OidcController
{
    public function redirect(string $provider, OidcService $oidcService): RedirectResponse
    {
        abort_unless($oidcService->isEnabled($provider), 404);

        $redirectUrl = route('auth.oidc.callback', ['provider' => $provider]);
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->redirectUrl($redirectUrl)->redirect();
    }

    public function callback(string $provider, OidcService $oidcService): RedirectResponse|Redirector
    {
        abort_unless($oidcService->isEnabled($provider), 404);

        $redirectUrl = route('auth.oidc.callback', ['provider' => $provider]);
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = $driver->redirectUrl($redirectUrl)->user();

            if (auth()->check()) {
                /** @var User $user */
                $user = auth()->user();
                $oidcService->linkProvider($user, $provider, $socialiteUser);

                return redirect(EditProfile::getUrl());
            }

            $user = $oidcService->handleCallback($provider, $socialiteUser);
            Auth::login($user, remember: true);

            return redirect()->intended(Filament::getUrl());
        } catch (Throwable $e) {
            Notification::make()
                ->warning()
                ->title(__('profile.oidc.auth_failed_title', ['provider' => ucfirst($provider)]))
                ->send();

            Log::error("OIDC Login failed for provider {$provider}: ".$e->getMessage());

            return redirect(Filament::getLoginUrl());
        }
    }
}
