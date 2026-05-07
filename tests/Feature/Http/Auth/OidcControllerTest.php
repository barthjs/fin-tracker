<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function oidcSocialiteUser(): SocialiteUser
{
    $user = new SocialiteUser;
    $user->map([
        'id' => 'oidc-999',
        'nickname' => 'janedoe',
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'avatar' => null,
    ]);
    $user->setRaw(['given_name' => 'Jane', 'family_name' => 'Doe']);

    return $user;
}

function fakeOidcDriver(): AbstractProvider
{
    /** @var AbstractProvider $driver */
    $driver = Mockery::mock(AbstractProvider::class);
    $driver->shouldReceive('redirectUrl')->andReturnSelf();

    return $driver;
}

it('redirects to the configured provider', function (): void {
    $driver = fakeOidcDriver();
    $driver->shouldReceive('redirect')->andReturn(redirect('https://provider.test/authorize'));
    Socialite::shouldReceive('driver')->with('oidc')->andReturn($driver);

    get(route('auth.oidc.redirect', ['provider' => 'oidc']))
        ->assertRedirect('https://provider.test/authorize');
});

it('returns 404 for a disabled provider', function (): void {
    config()->set('services.authentik.oidc_enabled', false);

    get(route('auth.oidc.redirect', ['provider' => 'authentik']))
        ->assertNotFound();
});

it('logs in a new user through the callback', function (): void {
    $driver = fakeOidcDriver();
    $driver->shouldReceive('user')->andReturn(oidcSocialiteUser());
    Socialite::shouldReceive('driver')->with('oidc')->andReturn($driver);

    get(route('auth.oidc.callback', ['provider' => 'oidc']))
        ->assertRedirect(Filament::getUrl());

    $this->assertAuthenticated();
    $this->assertDatabaseHas('sys_users', ['email' => 'jane@example.com']);
});

it('links a provider for an authenticated user', function (): void {
    $user = User::factory()->verified()->create();
    actingAs($user);

    $driver = fakeOidcDriver();
    $driver->shouldReceive('user')->andReturn(oidcSocialiteUser());
    Socialite::shouldReceive('driver')->with('oidc')->andReturn($driver);

    get(route('auth.oidc.callback', ['provider' => 'oidc']))
        ->assertRedirect(EditProfile::getUrl());

    $this->assertDatabaseHas('sys_user_providers', [
        'user_id' => $user->id,
        'provider_name' => 'oidc',
        'provider_id' => 'oidc-999',
    ]);
});

it('handles an error response from the provider', function (): void {
    get(route('auth.oidc.callback', ['provider' => 'oidc', 'error' => 'access_denied', 'error_description' => 'denied']))
        ->assertRedirect(Filament::getLoginUrl());
});

it('handles an exception during the callback', function (): void {
    $driver = fakeOidcDriver();
    $driver->shouldReceive('user')->andThrow(new RuntimeException('oauth failed'));
    Socialite::shouldReceive('driver')->with('oidc')->andReturn($driver);

    get(route('auth.oidc.callback', ['provider' => 'oidc']))
        ->assertRedirect(Filament::getLoginUrl());

    $this->assertGuest();
});
