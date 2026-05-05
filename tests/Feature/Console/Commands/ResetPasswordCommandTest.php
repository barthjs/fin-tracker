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
