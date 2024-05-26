<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\BankAccountTransaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(3)->create();
        foreach ($users as $user) {
            TransactionCategory::factory(10)->create(['user_id' => $user->user_id]);
            BankAccount::factory(5)->create(['user_id' => $user->user_id]);
            BankAccountTransaction::factory(100)->create();
        }
    }
}
