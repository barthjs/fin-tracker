<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\User;
use App\Models\UserProvider;
use App\Services\Oidc\OidcService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Two\User as SocialiteUser;
use RuntimeException;

beforeEach(function (): void {
    $this->service = resolve(OidcService::class);
});

/**
 * @param  array<string, mixed>  $attributes
 * @param  array<string, mixed>  $raw
 */
function socialiteUser(array $attributes = [], array $raw = ['given_name' => 'John', 'family_name' => 'Doe', 'locale' => 'de']): SocialiteUser
{
    $user = new SocialiteUser;
    $user->map(array_merge([
        'id' => 'oidc-123',
        'nickname' => 'johndoe',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'avatar' => null,
    ], $attributes));
    $user->setRaw($raw);

    return $user;
}

test('it returns only enabled providers', function (): void {
    Config::set('services.authelia.oidc_enabled', true);
    Config::set('services.authelia.label', 'Authelia');
    Config::set('services.authentik.oidc_enabled', false);

    $enabled = $this->service->getEnabledProviders();

    expect($enabled)->toHaveKey('authelia')
        ->and($enabled)->not->toHaveKey('authentik')
        ->and($enabled['authelia']['label'])->toBe('Authelia');
});

test('isEnabled reflects the provider configuration', function (): void {
    Config::set('services.authelia.oidc_enabled', true);
    Config::set('services.gitea.oidc_enabled', false);

    expect($this->service->isEnabled('authelia'))->toBeTrue()
        ->and($this->service->isEnabled('gitea'))->toBeFalse();
});

test('handleCallback returns the user of an already linked provider', function (): void {
    $user = User::factory()->create();
    UserProvider::query()->create([
        'user_id' => $user->id,
        'provider_name' => 'oidc',
        'provider_id' => 'oidc-123',
    ]);

    expect($this->service->handleCallback('oidc', socialiteUser())->id)->toBe($user->id);
});

test('handleCallback throws when registration is disabled', function (): void {
    Config::set('app.allow_registration', false);

    expect(fn () => $this->service->handleCallback('oidc', socialiteUser()))
        ->toThrow(RuntimeException::class, 'Registration is disabled');
});

test('handleCallback throws on an email collision', function (): void {
    User::factory()->create(['email' => 'john@example.com']);

    expect(fn () => $this->service->handleCallback('oidc', socialiteUser()))
        ->toThrow(RuntimeException::class, 'Email collision');
});

test('handleCallback creates a new user and provider link', function (): void {
    $user = $this->service->handleCallback('oidc', socialiteUser());

    expect($user->first_name)->toBe('John')
        ->and($user->last_name)->toBe('Doe')
        ->and($user->email)->toBe('john@example.com')
        ->and($user->is_verified)->toBeTrue();

    $this->assertDatabaseHas('sys_user_providers', [
        'user_id' => $user->id,
        'provider_name' => 'oidc',
        'provider_id' => 'oidc-123',
    ]);
});

test('handleCallback splits the full name when structured names are missing', function (): void {
    $user = $this->service->handleCallback('oidc', socialiteUser(['name' => 'Jane Smith'], raw: []));

    expect($user->first_name)->toBe('Jane')
        ->and($user->last_name)->toBe('Smith');
});

test('handleCallback generates a unique username on collision', function (): void {
    User::factory()->create(['username' => 'johndoe']);

    $user = $this->service->handleCallback('oidc', socialiteUser(['email' => 'other@example.com']));

    expect($user->username)->toBe('johndoe1');
});

test('linkProvider links a provider to an existing user', function (): void {
    $user = User::factory()->create();

    $this->service->linkProvider($user, 'oidc', socialiteUser());

    $this->assertDatabaseHas('sys_user_providers', [
        'user_id' => $user->id,
        'provider_name' => 'oidc',
        'provider_id' => 'oidc-123',
    ]);
});

test('handleCallback ignores an invalid avatar url', function (): void {
    $user = $this->service->handleCallback('oidc', socialiteUser(['avatar' => 'not-a-url']));

    expect($user->avatar)->toBeNull();
});

test('handleCallback ignores a failed avatar download', function (): void {
    Http::fake(['*' => Http::response('', 404)]);

    $user = $this->service->handleCallback('oidc', socialiteUser(['avatar' => 'https://img.test/a.png']));

    expect($user->avatar)->toBeNull();
});

test('handleCallback ignores a non-image avatar download', function (): void {
    Http::fake(['https://img.test/*' => Http::response('plain text, not an image', 200)]);

    $user = $this->service->handleCallback('oidc', socialiteUser(['avatar' => 'https://img.test/notimage.txt']));

    expect($user->avatar)->toBeNull();
});

test('handleCallback generates a fallback username when no name is available', function (): void {
    $user = $this->service->handleCallback('oidc', socialiteUser([
        'id' => 'oidc-empty',
        'nickname' => null,
        'name' => null,
        'email' => null,
    ], raw: []));

    expect($user->username)->toBe('user');
});

test('handleCallback stores a valid downloaded avatar', function (): void {
    Storage::fake('public');

    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
    Http::fake(['https://img.test/*' => Http::response($png, 200, ['Content-Type' => 'image/png'])]);

    $user = $this->service->handleCallback('oidc', socialiteUser(['avatar' => 'https://img.test/avatar.png']));

    expect($user->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar);
});
