<?php

declare(strict_types=1);

namespace App\Services\Oidc;

use App\Models\User;
use App\Models\UserProvider;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\User as SocialiteUser;
use RuntimeException;
use Throwable;

final readonly class OidcService
{
    /**
     * @return array<array{label: string}>
     */
    public function getEnabledProviders(): array
    {
        $enabledProviders = [];

        foreach (config()->array('services') as $provider => $config) {
            /** @phpstan-ignore-next-line */
            if (isset($config['oidc_enabled']) && $config['oidc_enabled']) {
                $enabledProviders[$provider] = [
                    'label' => config()->string(sprintf('services.%s.label', $provider)),
                ];
            }
        }

        return $enabledProviders;
    }

    public function isEnabled(string $provider): bool
    {
        return config()->boolean(sprintf('services.%s.oidc_enabled', $provider));
    }

    /**
     * Creates a new user from an OIDC provider.
     *
     * @throws RuntimeException
     */
    public function handleCallback(string $provider, SocialiteUser $socialiteUser): User
    {
        $userProvider = UserProvider::findForProvider($provider, $socialiteUser);
        if ($userProvider instanceof UserProvider) {
            return $userProvider->user;
        }

        throw_unless(config()->boolean('app.allow_registration'), RuntimeException::class, 'Registration is disabled');

        throw_if(User::query()->where('email', $socialiteUser->getEmail())->exists(), RuntimeException::class, 'Email collision');

        $avatarPath = $this->downloadAvatar($socialiteUser->getAvatar());

        try {
            return DB::transaction(function () use ($provider, $socialiteUser, $avatarPath): User {
                $user = $this->createUserFromSocialite($socialiteUser, $avatarPath);
                UserProvider::createForProvider($provider, $socialiteUser, $user);

                return $user;
            });
            // @codeCoverageIgnoreStart
        } catch (Throwable $throwable) {
            if (is_string($avatarPath)) {
                Storage::disk('public')->delete($avatarPath);
            }

            throw $throwable;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Links an OIDC provider to an existing user.
     */
    public function linkProvider(User $user, string $provider, SocialiteUser $socialiteUser): void
    {
        UserProvider::query()->updateOrCreate(
            ['provider_name' => $provider, 'provider_id' => $socialiteUser->getId()],
            ['user_id' => $user->id]
        );
    }

    private function downloadAvatar(?string $avatarUrl): ?string
    {
        if ($avatarUrl === null || ! filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
            return null;
        }

        $tmpDirectory = storage_path('app/private/livewire-tmp');
        // @codeCoverageIgnoreStart
        if (! file_exists($tmpDirectory)) {
            mkdir($tmpDirectory, 0755, true);
        }

        // @codeCoverageIgnoreEnd

        $tmpPath = $tmpDirectory.'/'.Str::uuid()->toString();

        try {
            $response = Http::get($avatarUrl);
            if (! $response->successful()) {
                return null;
            }

            file_put_contents($tmpPath, $response->body());

            $file = new File($tmpPath);
            $validator = Validator::make(
                ['avatar' => $file],
                ['avatar' => ['image', 'max:1024']]
            );

            if ($validator->fails()) {
                return null;
            }

            $extension = $file->extension() ?: 'jpg';
            $filename = Str::ulid().'.'.$extension;
            $path = 'users/avatars/'.$filename;

            Storage::disk('public')->putFileAs('users/avatars', $file, $filename);

            return $path;
            // @codeCoverageIgnoreStart
        } catch (Throwable) {
        }

        return null;
        // @codeCoverageIgnoreEnd
    }

    private function createUserFromSocialite(SocialiteUser $socialiteUser, ?string $avatarPath): User
    {
        /** @var array<string, mixed> $rawUser */
        $rawUser = $socialiteUser->getRaw();

        /** @var string|null $firstName */
        $firstName = $rawUser['given_name'] ?? null;
        /** @var string|null $lastName */
        $lastName = $rawUser['family_name'] ?? null;

        // Split the full name if OIDC fields are missing
        if ($firstName === null && $lastName === null) {
            $fullName = $socialiteUser->getName();
            if (is_string($fullName) && $fullName !== '') {
                $nameParts = explode(' ', $fullName, 2);
                $firstName = $nameParts[0];
                $lastName = $nameParts[1] ?? null;
            }
        }

        /** @var string|null $rawLocale */
        $rawLocale = $rawUser['locale'] ?? null;
        $locale = null;

        if (is_string($rawLocale)) {
            $shortLocale = mb_substr($rawLocale, 0, 2);
            if (in_array($shortLocale, ['en', 'de'], true)) {
                $locale = $shortLocale;
            }
        }

        return User::query()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $this->generateUniqueUsername($socialiteUser),
            'email' => $socialiteUser->getEmail(),
            'avatar' => $avatarPath,
            'locale' => $locale,
            'is_verified' => true,
            'is_active' => true,
            'is_admin' => false,
        ]);
    }

    private function generateUniqueUsername(SocialiteUser $socialiteUser): string
    {
        $base = $socialiteUser->getNickname()
            ?? $socialiteUser->getName()
            ?? Str::before($socialiteUser->getEmail() ?? '', '@');

        $base = Str::slug($base, '.');
        if (empty($base)) {
            $base = 'user';
        }

        $username = $base;
        $counter = 1;

        while (User::query()->where('username', $username)->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }
}
