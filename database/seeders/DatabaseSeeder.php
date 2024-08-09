<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\BankAccountTransaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
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
            'name' => 'admin',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('admin'),
            'remember_token' => null,
        ]);

        if (App::environment() === 'local') {
            $bank = BankAccount::factory(3)->create(['user_id' => $user->id]);
            $cat = TransactionCategory::factory(10)->create(['user_id' => $user->id]);
            foreach ($bank as $item) {
                for ($i = 0; $i < 10; $i++) {
                    $categoryId = $cat->random()->id;
                    BankAccountTransaction::factory()->create(['bank_account_id' => $item->id, 'category_id' => $categoryId]);
                }
            }
        }
    }
}
