<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Scopes\AccountScope;
use App\Models\Security;
use App\Models\Trade;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Seeds demo data for existing users and creates additional
     * test users with associated models.
     */
    public function run(): void
    {
        if (App::environment() === 'local') {
            $users = User::all();
            foreach ($users as $user) {
                $this->createTestValuesTransactions($user);
                $this->createTestValuesTrades($user);
            }

            for ($i = 1; $i <= 3; $i++) {
                $user = User::factory()->create(['email' => "test$i@example.com"]);
                $this->createTestValuesTransactions($user);
                $this->createTestValuesTrades($user);
            }
        }
    }

    /**
     * Generates test transaction data for a given user
     *
     *
     * - Creates 10 categories for the user.
     * - Creates 3 bank accounts for the user.
     * - Generates 216 transactions for each bank account, assigning them randomly to the transaction categories.
     * Total transactions for 4 users: 864
     * @param User $user The user for whom the test data is generated.
     * @return void
     */
    private function createTestValuesTransactions(User $user): void
    {
        $accounts = Account::factory(3)->create(['user_id' => $user->id]);
        $categories = Category::factory(10)->create(['user_id' => $user->id]);
        for ($y = 0; $y < 3; $y++) {
            for ($m = 1; $m <= 12; $m++) {
                foreach ($accounts as $account) {
                    for ($i = 0; $i < 2; $i++) {
                        $category = $categories->random();
                        $amount = fake()->numberBetween(0, 1000);
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


    /**
     * Generates test trades data for a given user
     *
     *
     * - Creates 10 categories for the user.
     * - Creates 3 bank accounts for the user.
     * - Generates 216 transactions for each bank account, assigning them randomly to the transaction categories.
     * Total transactions for 4 users: 864
     * @param User $user The user for whom the test data is generated.
     * @return void
     */
    private function createTestValuesTrades(User $user): void
    {
        $portfolios = Portfolio::factory(3)->create(['user_id' => $user->id]);
        $securities = Security::factory(10)->create(['user_id' => $user->id]);
        $accounts = Account::withoutGlobalScopes([AccountScope::class])->whereUserId($user->id)->get();
        for ($y = 0; $y < 3; $y++) {
            for ($m = 1; $m <= 12; $m++) {
                foreach ($portfolios as $portfolio) {
                    for ($i = 0; $i < 2; $i++) {
                        $security = $securities->random();
                        $account = $accounts->random();
                        Trade::factory()->create([
                            'date_time' => Carbon::now()->subYears($y)->month($m),
                            'account_id' => $account->id,
                            'portfolio_id' => $portfolio->id,
                            'security_id' => $security->id,
                            'user_id' => $user->id
                        ]);
                    }
                }
            }
        }
    }
}
