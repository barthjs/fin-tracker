<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetPassword extends Command
{
    protected $signature = 'app:reset-password {emailOrUsername?}';

    protected $description = 'Reset the password for a user by email or username';

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
            $user = User::where('email', $emailOrUsername)->first();
        } else {
            $user = User::where('name', $emailOrUsername)->first();
        }

        if (empty($user)) {
            $this->error('User not found');

            return 1;
        }

        if (! $this->confirm('Resetting password for user '.$user->name, true)) {
            return 1;
        }

        $password = $this->secret('Enter new password for user '.$user->name);

        $user->password = Hash::make($password);
        $user->save();

        $this->info('Password reset successfully');

        return 0;
    }
}
