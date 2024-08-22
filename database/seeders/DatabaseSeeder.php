<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\BankAccountTransaction;
use App\Models\TransactionCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
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
     * - Generates 60 transactions for each bank account, assigning them randomly to the transaction categories.
     * Total transactions for 4 users: 2160
     * @param User $user The user for whom the test data is generated.
     * @return void
     */
    private function createTestValues(User $user): void
    {
        $bank = BankAccount::factory(3)->create(['user_id' => $user->id]);
        $cat = TransactionCategory::factory(10)->create(['user_id' => $user->id]);
        foreach ($bank as $item) {
            for ($y = 0; $y < 3; $y++) {
                for ($m = 1; $m <= 12; $m++) {
                    for ($i = 0; $i < 5; $i++) {
                        $category = $cat->random();
                        $amount = fake()->randomFloat(2, 0, 10000);
                        $amount *= ($category->type == "expense") ? -1 : 1;
                        BankAccountTransaction::factory()->create([
                            'date' => Carbon::today()->subYears($y)->month($m),
                            'amount' => $amount,
                            'bank_account_id' => $item->id,
                            'category_id' => $category->id
                        ]);
                    }
                }
            }
        }
        Artisan::call('app:create-statistic');
    }
}
