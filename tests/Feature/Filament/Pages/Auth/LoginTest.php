<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Livewire\livewire;

it('renders the login page', function () {
    livewire(Login::class)
        ->assertOk()
        ->assertSee(__('user.fields.username_or_email'))
        ->assertSee(__('filament-panels::auth/pages/login.form.password.label'));
});

it('redirects unauthenticated users to the login page', function () {
    get(Filament::getUrl())
        ->assertRedirect(Filament::getLoginUrl());
});

it('can log in with email', function () {
    $user = User::factory()->create();

    livewire(Login::class)
        ->fillForm([
            'login' => $user->email,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect(Filament::getUrl());

    assertAuthenticatedAs($user);
});

it('can log in with username', function () {
    $user = User::factory()->create();

    livewire(Login::class)
        ->fillForm([
            'login' => $user->username,
            'password' => 'password',
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect(Filament::getUrl());

    assertAuthenticatedAs($user);
});

it('shows validation error on failed login', function () {
    livewire(Login::class)
        ->fillForm([
            'login' => 'wrong-user',
            'password' => 'wrong-password',
        ])
        ->call('authenticate')
        ->assertHasErrors(['data.login']);

    assertGuest();
});

test('oidc redirect route redirects to provider authorization endpoint', function () {
    $baseUrl = config()->string('services.oidc.base_url');
    Http::fake([
        config()->string('services.oidc.base_url').'/.well-known/openid-configuration' => Http::response([
            'authorization_endpoint' => $baseUrl.'/auth',
            'token_endpoint' => $baseUrl.'/token',
            'userinfo_endpoint' => $baseUrl.'/userinfo',
        ]),
    ]);

    get(route('auth.oidc.redirect', ['provider' => 'oidc']))
        ->assertRedirectContains($baseUrl.'/auth');
});

test('oidc callback creates new user and redirects to dashboard', function () {
    Socialite::fake('oidc', (new SocialiteUser)->map([
        'id' => 1,
        'name' => 'Test User',
        'email' => 'user@example.com',
    ]));

    get(route('auth.oidc.callback', ['provider' => 'oidc']))
        ->assertRedirect(Filament::getUrl());

    assertDatabaseHas('sys_users', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'test.user',
        'email' => 'user@example.com',
    ]);

    assertDatabaseHas('sys_user_providers', [
        'provider_name' => 'oidc',
        'provider_id' => '1',
    ]);

    assertAuthenticated();
});

test('oidc callback prevents login on email collision', function () {
    User::factory()->create(['email' => 'collision@example.com']);

    Socialite::fake('oidc', (new SocialiteUser)->map([
        'id' => 1,
        'name' => 'Test User',
        'email' => 'collision@example.com',
    ]));

    get(route('auth.oidc.callback', ['provider' => 'oidc']))
        ->assertRedirect(Filament::getLoginUrl());

    assertGuest();
});

test('users can logout', function () {
    asUser();

    post(Filament::getLogoutUrl())
        ->assertRedirect(Filament::getLoginUrl());

    assertGuest();
});
