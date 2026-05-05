<?php

declare(strict_types=1);

use App\Console\Commands\Disable2FACommand;
use App\Models\User;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    $this->user = User::factory()->withTwoFactor()->create();
});

it('disables 2FA via email', function (): void {
    artisan(Disable2FACommand::class, ['emailOrUsername' => $this->user->email])
        ->expectsConfirmation('Disabling 2FA for user '.$this->user->username, 'yes')
        ->assertSuccessful();

    $this->user->refresh();
    expect($this->user->two_factor_secret)->toBeNull()
        ->and($this->user->two_factor_recovery_codes)->toBeEmpty()
        ->and($this->user->two_factor_enabled_at)->toBeNull();
});

it('disables 2FA via username', function (): void {
    artisan(Disable2FACommand::class, ['emailOrUsername' => $this->user->username])
        ->expectsConfirmation('Disabling 2FA for user '.$this->user->username, 'yes')
        ->assertSuccessful();

    $this->user->refresh();
    expect($this->user->app_authentication_secret)->toBeNull()
        ->and($this->user->app_authentication_recovery_codes)->toBeEmpty();
});

it('asks for the email or username when not provided', function (): void {
    artisan(Disable2FACommand::class)
        ->expectsQuestion('Enter email or username to reset password', $this->user->email)
        ->expectsConfirmation('Disabling 2FA for user '.$this->user->username, 'yes')
        ->assertSuccessful();

    expect($this->user->refresh()->two_factor_secret)->toBeNull();
});

it('fails when the user is not found', function (): void {
    artisan(Disable2FACommand::class, ['emailOrUsername' => 'non-existent'])
        ->expectsOutputToContain('User not found')
        ->assertFailed();
});

it('keeps 2FA when the confirmation is declined', function (): void {
    artisan(Disable2FACommand::class, ['emailOrUsername' => $this->user->username])
        ->expectsConfirmation('Disabling 2FA for user '.$this->user->username, 'no')
        ->assertFailed();

    expect($this->user->refresh()->app_authentication_secret)->not->toBeNull()
        ->and($this->user->refresh()->app_authentication_recovery_codes)->not->toBeNull();
});
