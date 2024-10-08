<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!User::whereIsAdmin(1)->first()) {
            User::firstOrCreate(['name' => 'admin'],
                [
                    'first_name' => 'Admin',
                    'last_name' => 'Admin',
                    'email' => 'admin@example.com',
                    'password' => Hash::make('admin'),
                    'is_admin' => true,
                    'active' => true,
                ]
            );
        }
        // Artisan::call('app:create-statistic');
    }
}
