<?php

declare(strict_types=1);

namespace Database\Seeders;

use App;
use App\Models\Account;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (App::environment('testing')) {
            return;
        }

        if (User::where('is_admin', '=', true)->first() === null) {
            /** @var User $user */
            $user = User::firstOrCreate(['username' => 'admin', 'email' => 'admin@example.com'],
                [
                    'first_name' => 'Admin',
                    'last_name' => 'Admin',
                    'password' => Hash::make('admin'),
                    'is_active' => true,
                    'is_verified' => ! App::isProduction(),
                    'is_admin' => true,
                ]
            );

            $this->generateDefaults($user);
        }

        if (! App::isLocal()) {
            return;
        }

        /** @var User $user */
        $user = User::firstOrCreate(['username' => 'user', 'email' => 'user@example.com'],
            [
                'first_name' => 'User',
                'last_name' => 'User',
                'password' => Hash::make('user'),
                'is_active' => true,
                'is_verified' => true,
                'is_admin' => false,
            ]);

        $this->generateDefaults($user);

        $this->call([
            DemoSeeder::class,
        ]);
    }

    private function generateDefaults(User $user): void
    {
        Account::getOrCreateDefaultAccount($user);
        Portfolio::getOrCreateDefaultPortfolio($user);
        Security::getOrCreateDefaultSecurity($user);
        Category::getOrCreateDefaultCategory($user);
    }
}
