<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\BankAccountTransaction;
use App\Models\TransactionCategory;
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
        $user = User::firstOrCreate([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'remember_token' => null,
            'username' => 'admin'
        ]);
        $bank = BankAccount::factory(3)->create(['user_id' => $user->user_id]);
        $cat = TransactionCategory::factory(10)->create(['user_id' => $user->user_id]);
        BankAccountTransaction::factory(10)->create(['bank_account_id' => $bank->random()->bank_account_id, 'category_id' => $cat->random()->category_id]);
    }
}
