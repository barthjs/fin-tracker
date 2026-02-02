<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use App\Services\Oidc\OidcService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Throwable;

final readonly class OidcController
{
    public function redirect(string $provider, OidcService $oidcService): RedirectResponse
    {
        $provider = mb_strtolower($provider);

        abort_unless($oidcService->isEnabled($provider), 404);

        $redirectUrl = route('auth.oidc.callback', ['provider' => $provider]);
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->redirectUrl($redirectUrl)->redirect();
    }

    public function callback(Request $request, string $provider, OidcService $oidcService): RedirectResponse
    {
        $provider = mb_strtolower($provider);

        if ($request->has('error')) {
            $this->setNotificationLocale($request);

            Notification::make()
                ->warning()
                ->title(__('profile.oidc.auth_failed_title', ['provider' => ucfirst($provider)]))
                ->body($request->query('error_description', ''))
                ->send();

            return redirect()->intended(Filament::getLoginUrl());
        }

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
            $this->setNotificationLocale($request);

            Notification::make()
                ->warning()
                ->title(__('profile.oidc.auth_failed_title', ['provider' => ucfirst($provider)]))
                ->send();

            Log::error("OIDC Login failed for provider $provider: ".$e->getMessage());

            return redirect()->intended(Filament::getLoginUrl());
        }
    }

    private function setNotificationLocale(Request $request): void
    {
        $user = auth()->user();

        /** @var string $locale */
        $locale = $user->locale
            ?? $request->getPreferredLanguage(['de', 'en'])
            ?? config('app.locale');

        app()->setLocale($locale);
    }
}
