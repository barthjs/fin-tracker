<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('disable-2fa {emailOrUsername?}')]
#[Description('Disable 2FA for a user')]
final class Disable2FACommand extends Command
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

        if (! $this->confirm('Disabling 2FA for user '.$user->username, true)) {
            return self::FAILURE;
        }

        $user->update([
            'app_authentication_secret' => null,
            'app_authentication_recovery_codes' => null,
        ]);

        $this->info('2FA disabled successfully');

        return self::SUCCESS;
    }
}
