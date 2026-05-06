<?php

declare(strict_types=1);

use App\Enums\ApiAbility;
use App\Filament\Pages\Auth\EditProfile;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    asUser();

    $this->user = auth()->user();
});

it('renders the profile page', function (): void {
    livewire(EditProfile::class)
        ->assertOk();
});

it('can update the name and the timezone', function (): void {
    livewire(EditProfile::class)
        ->fillForm([
            'first_name' => 'New',
            'last_name' => 'Name',
            'timezone' => 'Europe/Berlin',
        ])
        ->call('save')
        ->assertHasNoErrors();
});

it('redirects unverified users to the profile page', function (): void {
    $this->user->update(['is_verified' => false]);

    get(Filament::getUrl())
        ->assertRedirect(EditProfile::getUrl());
});

it('verifies an unverified user when they set a password', function (): void {
    $this->user->update(['is_verified' => false]);

    livewire(EditProfile::class)
        ->fillForm([
            'currentPassword' => 'password',
            'password' => 'password',
            'passwordConfirmation' => 'password',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect(Filament::getUrl());

    expect($this->user->refresh()->is_verified)->toBeTrue();
});

it('requires the current password to change the username or email if a password is set', function (): void {
    livewire(EditProfile::class)
        ->fillForm([
            'username' => 'new.username',
            'currentPassword' => '',
        ])
        ->call('save')
        ->assertHasErrors(['data.currentPassword']);

    livewire(EditProfile::class)
        ->fillForm([
            'email' => '',
            'currentPassword' => '',
        ])
        ->call('save')
        ->assertHasErrors(['data.currentPassword']);
});

it('does not require the current password if the user has no password set', function (): void {
    $this->user->update(['password' => null]);
    $this->user->providers()->create(['provider_name' => 'oidc', 'provider_id' => '1']);

    livewire(EditProfile::class)
        ->fillForm([
            'username' => 'new.username',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('can remove a linked OIDC provider', function (): void {
    $provider = $this->user->providers()->create(['provider_name' => 'oidc', 'provider_id' => '1']);

    livewire(EditProfile::class)
        ->callAction('removeProvider', arguments: ['id' => $provider->id])
        ->assertHasNoErrors();

    assertDatabaseMissing('sys_user_providers', ['id' => $provider->id]);
});

it('disables provider removal if it is the only login method', function (): void {
    $this->user->update(['password' => null]);
    $this->user->providers()->create(['provider_name' => 'oidc', 'provider_id' => '123']);

    livewire(EditProfile::class)
        ->assertActionDisabled('removeProvider');
});

it('can create an api token with specific abilities', function (): void {
    $ability = ApiAbility::ACCOUNT;

    livewire(EditProfile::class)
        ->callAction('createApiToken', [
            'name' => 'Test Token',
            'abilities' => [
                $ability->read() => true,
            ],
        ])
        ->assertHasNoFormErrors();

    $user = auth()->user();

    expect($user->tokens)->toHaveCount(1)
        ->and($user->tokens->first()->name)->toBe('Test Token')
        ->and($user->tokens->first()->abilities)->toContain($ability->read());
});

it('requires at least one ability when creating an api token', function (): void {
    livewire(EditProfile::class)
        ->callAction('createApiToken', [
            'name' => 'No Abilities',
            'abilities' => [],
        ])
        ->assertHasActionErrors(['name']);

    expect(auth()->user()->tokens()->count())->toBe(0);
});

it('can create an api token with an expiry date', function (): void {
    livewire(EditProfile::class)
        ->callAction('createApiToken', [
            'name' => 'Expiring Token',
            'abilities' => [ApiAbility::ACCOUNT->read() => true],
            'expires_at' => today()->addMonth()->toDateString(),
        ])
        ->assertHasNoFormErrors();

    $token = auth()->user()->tokens()->where('name', 'Expiring Token')->first();

    expect($token)->not->toBeNull()
        ->and($token->expires_at)->not->toBeNull();
});

it('automatically selects read ability when write is selected', function (): void {
    $ability = ApiAbility::PORTFOLIO;

    livewire(EditProfile::class)
        ->mountAction('createApiToken')
        ->fillForm([
            'abilities.'.$ability->write() => true,
        ])
        ->assertSchemaStateSet([
            'abilities.'.$ability->read() => true,
        ]);
});

it('can delete an api token', function (): void {
    $this->user->createToken('Delete Me', [ApiAbility::TRANSACTION->read()]);
    $token = $this->user->tokens->first();

    livewire(EditProfile::class)
        ->callAction('deleteApiToken', arguments: ['token' => $token->id])
        ->assertHasNoErrors();

    assertDatabaseMissing('sys_personal_access_tokens', ['id' => $token->id]);
});

it('displays the active user sessions on the profile page', function (): void {
    $this->startSession();

    DB::table(config()->string('session.table'))->insert([
        'id' => 'dummy_session_id',
        'user_id' => $this->user->id,
        'ip_address' => '1.2.3.4',
        'user_agent' => 'Firefox',
        'payload' => base64_encode('data'),
        'last_activity' => now()->timestamp,
    ]);

    livewire(EditProfile::class)
        ->assertOk()
        ->assertSee('1.2.3.4')
        ->assertSee('Firefox');
});

it('requires the correct password to log out other sessions', function (): void {
    $this->startSession();

    DB::table(config()->string('session.table'))->insert([
        'id' => 'other_session',
        'user_id' => $this->user->id,
        'ip_address' => '1.2.3.4',
        'user_agent' => 'Firefox',
        'payload' => base64_encode('data'),
        'last_activity' => now()->timestamp,
    ]);

    livewire(EditProfile::class)
        ->callAction('logoutOtherBrowserSessions', ['currentPassword' => 'wrong-password'])
        ->assertHasActionErrors(['currentPassword']);

    expect(
        DB::table(config()->string('session.table'))->where('id', 'other_session')->exists()
    )->toBeTrue();
});

it('does not require a password to log out other sessions when the user has not password set', function (): void {
    $this->user->update(['password' => null]);
    $this->startSession();
    DB::table(config()->string('session.table'))->insert([
        'id' => 'other_session',
        'user_id' => $this->user->id,
        'ip_address' => '1.2.3.4',
        'user_agent' => 'Firefox',
        'payload' => base64_encode('data'),
        'last_activity' => now()->timestamp,
    ]);

    livewire(EditProfile::class)
        ->callAction('logoutOtherBrowserSessions')
        ->assertHasNoErrors();

    expect(
        DB::table(config()->string('session.table'))->where('id', 'other_session')->exists()
    )->toBeFalse();
});

it('logs out other browser sessions', function (): void {
    $this->startSession();

    DB::table(config()->string('session.table'))->insert([
        'id' => 'other_session_id',
        'user_id' => $this->user->id,
        'ip_address' => '5.6.7.8',
        'user_agent' => 'Chrome',
        'payload' => base64_encode('data'),
        'last_activity' => time(),
    ]);

    livewire(EditProfile::class)
        ->callAction('logoutOtherBrowserSessions', data: ['currentPassword' => 'password'])
        ->assertHasNoErrors();

    assertDatabaseMissing(config()->string('session.table'), ['id' => 'other_session_id']);
});

it('deletes the user account after password confirmation', function (): void {
    livewire(EditProfile::class)
        ->callAction('deleteUserAccount', data: [
            'currentPassword' => 'password',
        ])
        ->assertRedirect(Filament::getLoginUrl());

    assertDatabaseMissing('sys_users', ['id' => $this->user->id]);
});
