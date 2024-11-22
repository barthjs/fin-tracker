<?php declare(strict_types=1);

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
                    'verified' => true,
                    'is_admin' => true,
                    'active' => true,
                ]
            );
        }
    }
}
