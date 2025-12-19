<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

final class Disable2FACommand extends Command
{
    protected $signature = 'app:disable-2fa {emailOrUsername?}';

    protected $description = 'Disable 2FA for a user by email or username';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $emailOrUsername = $this->argument('emailOrUsername');
        if (empty($emailOrUsername)) {
            $emailOrUsername = $this->ask('Enter email or username to reset password');
        }

        if (filter_var($emailOrUsername, FILTER_VALIDATE_EMAIL)) {
            $user = User::where('email', '=', $emailOrUsername)->first();
        } else {
            $user = User::where('username', '=', $emailOrUsername)->first();
        }

        if ($user === null) {
            $this->error('User not found');

            return 1;
        }

        if (! $this->confirm('Disabling 2FA for user '.$user->username, true)) {
            return 1;
        }

        $user->update([
            'app_authentication_secret' => null,
            'app_authentication_recovery_codes' => null,
        ]);

        $this->info('2FA disabled successfully');

        return 0;
    }
}
