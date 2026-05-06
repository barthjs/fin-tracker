<?php

declare(strict_types=1);

use App\Console\Commands\ResetPasswordCommand;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    $this->user = User::factory()->create([
        'username' => 'user',
        'email' => 'user@example.com',
        'password' => Hash::make('oldpassword'),
    ]);
});

it('resets the password via email', function (): void {
    $newPassword = 'newpassword123';

    artisan(ResetPasswordCommand::class, ['emailOrUsername' => $this->user->email])
        ->expectsConfirmation('Resetting password for user '.$this->user->username, 'yes')
        ->expectsQuestion('Enter new password for user: '.$this->user->username, $newPassword)
        ->expectsQuestion('Confirm password', $newPassword)
        ->assertSuccessful();

    $this->user->refresh();
    expect(Hash::check($newPassword, $this->user->password))->toBeTrue();
});

it('resets the password via username', function (): void {
    $newPassword = 'newpassword123';

    artisan(ResetPasswordCommand::class, ['emailOrUsername' => $this->user->username])
        ->expectsConfirmation('Resetting password for user '.$this->user->username, 'yes')
        ->expectsQuestion('Enter new password for user: '.$this->user->username, $newPassword)
        ->expectsQuestion('Confirm password', $newPassword)
        ->assertSuccessful();

    $this->user->refresh();
    expect(Hash::check($newPassword, $this->user->password))->toBeTrue();
});

it('fails when user is not found', function (): void {
    artisan(ResetPasswordCommand::class, ['emailOrUsername' => 'nonexistent@example.com'])
        ->expectsOutput('User not found')
        ->assertFailed();
});

it('asks for the user when no argument is given', function (): void {
    artisan(ResetPasswordCommand::class)
        ->expectsQuestion('Enter email or username to reset password', $this->user->username)
        ->expectsConfirmation('Resetting password for user '.$this->user->username, 'yes')
        ->expectsQuestion('Enter new password for user: '.$this->user->username, 'newpassword123')
        ->expectsQuestion('Confirm password', 'newpassword123')
        ->assertSuccessful();
});

it('cancels when the confirmation is declined', function (): void {
    artisan(ResetPasswordCommand::class, ['emailOrUsername' => $this->user->username])
        ->expectsConfirmation('Resetting password for user '.$this->user->username, 'no')
        ->assertSuccessful();

    expect(Hash::check('oldpassword', $this->user->refresh()->password))->toBeTrue();
});

it('retries when the password is empty or does not match', function (): void {
    artisan(ResetPasswordCommand::class, ['emailOrUsername' => $this->user->username])
        ->expectsConfirmation('Resetting password for user '.$this->user->username, 'yes')
        ->expectsQuestion('Enter new password for user: '.$this->user->username, '')
        ->expectsOutput('Password cannot be empty')
        ->expectsQuestion('Enter new password for user: '.$this->user->username, 'first')
        ->expectsQuestion('Confirm password', 'second')
        ->expectsOutput('Passwords do not match')
        ->expectsQuestion('Enter new password for user: '.$this->user->username, 'final-password')
        ->expectsQuestion('Confirm password', 'final-password')
        ->assertSuccessful();

    expect(Hash::check('final-password', $this->user->refresh()->password))->toBeTrue();
});
