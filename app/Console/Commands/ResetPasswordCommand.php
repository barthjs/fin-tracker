<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

#[Signature('reset-password {emailOrUsername?}')]
#[Description('Reset the password for a user')]
final class ResetPasswordCommand extends Command
{
    public function handle(): int
    {
        $emailOrUsername = $this->argument('emailOrUsername');
        if (empty($emailOrUsername)) {
            $emailOrUsername = $this->ask('Enter email or username to reset password');
        }

        if (filter_var($emailOrUsername, FILTER_VALIDATE_EMAIL)) {
            $user = User::query()->where('email', '=', $emailOrUsername)->first();
        } else {
            $user = User::query()->where('username', '=', $emailOrUsername)->first();
        }

        if ($user === null) {
            $this->error('User not found');

            return self::FAILURE;
        }

        if (! $this->confirm('Resetting password for user '.$user->username, true)) {
            return self::SUCCESS;
        }

        $password = '';
        while ($password === '' || $password === '0') {
            /** @var string|null $inputPassword */
            $inputPassword = $this->secret('Enter new password for user: '.$user->username);
            if ($inputPassword === null || $inputPassword === '') {
                $this->error('Password cannot be empty');

                continue;
            }

            /** @var string|null $confirmPassword */
            $confirmPassword = $this->secret('Confirm password');
            if ($confirmPassword === null || $inputPassword !== $confirmPassword) {
                $this->error('Passwords do not match');

                continue;
            }

            $password = $inputPassword;
        }

        try {
            $user->password = Hash::make($password);
            $user->remember_token = null;
            $user->save();

            DB::table(config()->string('session.table'))
                ->where('user_id', $user->id)
                ->delete();

            $this->info('Password reset successfully');
        } catch (Exception $exception) {
            Log::error('Password reset failed for user: '.$user->username, ['exception' => $exception]);
            $this->error('An error occurred while resetting the password.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
