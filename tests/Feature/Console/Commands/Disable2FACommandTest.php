<?php

declare(strict_types=1);

use App\Console\Commands\Disable2FACommand;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->withTwoFactor()->create();
});

it('disables 2FA via email', function () {
    $this->artisan(Disable2FACommand::class, ['emailOrUsername' => $this->user->email])
        ->expectsConfirmation('Disabling 2FA for user '.$this->user->username, 'yes')
        ->assertExitCode(0);

    $this->user->refresh();
    expect($this->user->app_authentication_secret)->toBeNull()
        ->and($this->user->app_authentication_recovery_codes)->toBeEmpty();
});

it('disables 2FA via username', function () {
    $this->artisan(Disable2FACommand::class, ['emailOrUsername' => $this->user->username])
        ->expectsConfirmation('Disabling 2FA for user '.$this->user->username, 'yes')
        ->assertExitCode(0);

    $this->user->refresh();
    expect($this->user->app_authentication_secret)->toBeNull()
        ->and($this->user->app_authentication_recovery_codes)->toBeEmpty();
});
