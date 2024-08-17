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
        $admin = User::firstOrCreate([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'name' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
            'is_admin' => true,
            'active' => true,
        ]);

        if (App::environment() === 'local') {
            $this->createTestValues($admin);
            for ($i = 1; $i <= 3; $i++) {
                $user = User::factory()->create(['email' => "test$i@example.com"]);
                $this->createTestValues($user);
            }
        }
    }

    /**
     * Generates test data for a given user, including transaction categories, bank accounts, and transactions.
     *
     * - Creates 10 transaction categories for the user.
     * - Creates 3 bank accounts for the user.
     * - Generates 100 transactions for each bank account, assigning them randomly to the transaction categories.
     *
     * @param User $user The user for whom the test data is generated.
     * @return void
     */
    private function createTestValues(User $user): void
    {
        $bank = BankAccount::factory(3)->create(['user_id' => $user->id]);
        $cat = TransactionCategory::factory(10)->create(['user_id' => $user->id]);
        foreach ($bank as $item) {
            for ($i = 0; $i < 100; $i++) {
                $category = $cat->random();
                $amount = fake()->randomFloat(2, 0, 10000);
                $amount *= ($category->type->value == "Expenses") ? -1 : 1;
                BankAccountTransaction::factory()->create(['bank_account_id' => $item->id, 'amount' => $amount, 'category_id' => $category->id]);
            }
        }
    }
}
