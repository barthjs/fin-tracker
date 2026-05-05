<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\GetOrCreateDefaultAccount;
use App\Actions\GetOrCreateDefaultCategory;
use App\Actions\GetOrCreateDefaultPortfolio;
use App\Actions\GetOrCreateDefaultSecurity;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
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

        if (User::query()->where('is_admin', '=', true)->count() === 0) {
            User::query()->create([
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('admin'),
                'is_active' => true,
                'is_verified' => ! App::isProduction(),
                'is_admin' => true,
            ]) |> $this->generateDefaults(...);
        }

        if (! App::isLocal()) {
            return;
        }

        User::query()->firstOrCreate(['username' => 'user', 'email' => 'user@example.com'], [
            'first_name' => 'User',
            'last_name' => 'User',
            'password' => Hash::make('user'),
            'is_active' => true,
            'is_verified' => true,
            'is_admin' => false,
        ]) |> $this->generateDefaults(...);

        $this->call([
            DemoSeeder::class,
        ]);
    }

    private function generateDefaults(User $user): void
    {
        resolve(GetOrCreateDefaultAccount::class)($user);
        resolve(GetOrCreateDefaultPortfolio::class)($user);
        resolve(GetOrCreateDefaultSecurity::class)($user);
        resolve(GetOrCreateDefaultCategory::class)($user);
    }
}
