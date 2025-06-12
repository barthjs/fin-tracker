<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ResetPasswordCommand extends Command
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

        $password = '';
        while (empty($password)) {
            $password = $this->secret('Enter new password for user: '.$user->name);
            if (empty($password)) {
                $this->error('Password cannot be empty');

                continue;
            }

            $confirmPassword = $this->secret('Confirm password');
            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match');
                $password = '';
            }
        }

        try {
            $user->password = Hash::make($password);
            $user->remember_token = null;

            DB::table(config('session.table'))->where('user_id', $user->id)->delete();

            $user->save();

            $this->info('Password reset successfully');
        } catch (Exception $e) {
            Log::error('Password reset failed for user: '.$user->name, ['exception' => $e]);
            $this->error('An error occurred while resetting the password.');

            return 1;
        }

        return 0;
    }
}
