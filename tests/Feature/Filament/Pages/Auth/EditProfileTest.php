<?php

declare(strict_types=1);

use App\Filament\Pages\Auth\EditProfile;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

beforeEach(function () {
    asUser();

    $this->user = auth()->user();
    $this->user->update([
        'app_authentication_secret' => Hash::make(Str::random(10)),
        'app_authentication_recovery_codes' => [Str::random(10), Str::random(10)],
    ]);
});

it('renders the profile page', function () {
    livewire(EditProfile::class)
        ->assertOk();
});

it('redirects unverified users to the profile page', function () {
    $this->user->update(['is_verified' => false]);

    get(Filament::getUrl())
        ->assertRedirect(EditProfile::getUrl());
});

it('verifies an unverified user when they set a password', function () {
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

it('requires the current password to change the username if a password is set', function () {
    livewire(EditProfile::class)
        ->fillForm([
            'username' => 'new.username',
            'currentPassword' => '',
        ])
        ->call('save')
        ->assertHasErrors(['data.currentPassword']);
});

it('does not require the current password if the user has no password set', function () {
    $this->user->update(['password' => null]);
    $this->user->providers()->create(['provider_name' => 'oidc', 'provider_id' => '1']);

    livewire(EditProfile::class)
        ->fillForm([
            'username' => 'new.username',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('can remove a linked OIDC provider', function () {
    $provider = $this->user->providers()->create(['provider_name' => 'oidc', 'provider_id' => '1']);

    livewire(EditProfile::class)
        ->callAction('removeProvider', arguments: ['id' => $provider->id])
        ->assertHasNoErrors();

    assertDatabaseMissing('sys_user_providers', ['id' => $provider->id]);
});

it('disables provider removal if it is the only login method', function () {
    $this->user->update(['password' => null]);
    $this->user->providers()->create(['provider_name' => 'oidc', 'provider_id' => '123']);

    livewire(EditProfile::class)
        ->assertActionDisabled('removeProvider');
});

it('displays the active user sessions on the profile page', function () {
    $this->startSession();

    DB::table('sys_sessions')->insert([
        'id' => 'dummy_session_id',
        'user_id' => $this->user->id,
        'ip_address' => '1.2.3.4',
        'user_agent' => 'Firefox',
        'payload' => base64_encode('data'),
        'last_activity' => time(),
    ]);

    livewire(EditProfile::class)
        ->assertOk()
        ->assertSee('1.2.3.4')
        ->assertSee('Firefox');
});

it('deletes the user account after password confirmation', function () {
    livewire(EditProfile::class)
        ->callAction('deleteUserAccount', data: [
            'currentPassword' => 'password',
        ])
        ->assertRedirect(Filament::getLoginUrl());

    assertDatabaseMissing('sys_users', ['id' => $this->user->id]);
});
