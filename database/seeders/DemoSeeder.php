<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (App::environment() === 'local') {
            $users = User::all();
            foreach ($users as $user) {
                $this->createTestValues($user);
            }
            for ($i = 1; $i <= 3; $i++) {
                $user = User::factory()->create(['email' => "test$i@example.com"]);
                $this->createTestValues($user);
            }
            Artisan::call('app:create-statistic');
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
        $accounts = Account::factory(3)->create(['user_id' => $user->id]);
        $categories = Category::factory(10)->create(['user_id' => $user->id]);
        for ($y = 0; $y < 3; $y++) {
            for ($m = 1; $m <= 12; $m++) {
                foreach ($accounts as $account) {
                    for ($i = 0; $i < 2; $i++) {
                        $category = $categories->random();
                        $amount = fake()->randomFloat(2, 0, 1000);
                        $amount *= ($category->type->name == "expense") ? -1 : 1;
                        Transaction::factory()->create([
                            'date_time' => Carbon::now()->subYears($y)->month($m),
                            'amount' => $amount,
                            'account_id' => $account->id,
                            'category_id' => $category->id,
                            'user_id' => $user->id
                        ]);
                    }
                }
            }
        }
    }
}
