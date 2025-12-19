<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()
        ->create([
            'app_authentication_secret' => Str::random(10),
            'app_authentication_recovery_codes' => [Str::random(10), Str::random(10)],
        ]);
});

it('disables 2FA via email', function () {
    $this->artisan('app:disable-2fa', ['emailOrUsername' => $this->user->email])
        ->expectsConfirmation('Disabling 2FA for user '.$this->user->username, 'yes')
        ->assertExitCode(0);

    $this->user->refresh();
    expect($this->user->app_authentication_secret)->toBeNull()
        ->and($this->user->app_authentication_recovery_codes)->toBeEmpty();
});

it('disables 2FA via username', function () {
    $this->artisan('app:disable-2fa', ['emailOrUsername' => $this->user->username])
        ->expectsConfirmation('Disabling 2FA for user '.$this->user->username, 'yes')
        ->assertExitCode(0);

    $this->user->refresh();
    expect($this->user->app_authentication_secret)->toBeNull()
        ->and($this->user->app_authentication_recovery_codes)->toBeEmpty();
});
