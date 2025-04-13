<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TradeType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Portfolio;
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
     */
    public function run(): void
    {
        if (App::environment() === 'local') {
            $users = User::all();
            foreach ($users as $user) {
                $this->createTransactions($user);
                $this->createTrades($user);
            }
        }
    }

    /**
     * Generates test transaction data for a given user
     */
    private function createTransactions(User $user): void
    {
        $accounts = Account::factory(3)->create(['user_id' => $user->id]);
        $categories = Category::factory(10)->create(['user_id' => $user->id]);

        for ($m = 11; $m >= 0; $m--) {
            foreach ($accounts as $account) {
                for ($i = 0; $i < 2; $i++) {
                    $category = $categories->random();
                    $amount = fake()->numberBetween(10, 1000);
                    $amount *= ($category->type->name == 'expense') ? -1 : 2;

                    Transaction::factory()->create([
                        'date_time' => Carbon::now()->subMonths($m),
                        'amount' => $amount,
                        'account_id' => $account->id,
                        'category_id' => $category->id,
                        'user_id' => $user->id,
                    ]);
                }
            }
        }
    }

    /**
     * Generates test trades data for a given user
     */
    private function createTrades(User $user): void
    {
        $portfolios = Portfolio::factory(3)->create(['user_id' => $user->id]);
        Security::factory(10)->create(['user_id' => $user->id]);

        for ($m = 11; $m >= 0; $m--) {
            foreach ($portfolios as $portfolio) {
                for ($i = 0; $i < 2; $i++) {
                    $security = Security::withoutGlobalScopes()->whereUserId($user->id)->get()->random();
                    $account = Account::withoutGlobalScopes()->whereUserId($user->id)->get()->random();

                    if ((float) $security->total_quantity <= 0) {
                        $type = TradeType::BUY;
                    } else {
                        $type = fake()->randomElement(TradeType::cases());
                    }

                    $price = fake()->randomFloat(2, 1, 100);

                    if ($type == TradeType::BUY) {
                        if ($account->balance <= 0) {
                            continue;
                        }
                        $maxQuantity = $account->balance / $price;
                        $quantity = fake()->randomFloat(2, 1, $maxQuantity);
                    } else {
                        $quantity = fake()->randomFloat(2, 0, $security->total_quantity);
                    }

                    Trade::factory()->create([
                        'date_time' => Carbon::now()->subMonths($m),
                        'quantity' => $quantity,
                        'price' => $price,
                        'type' => $type->name,
                        'account_id' => $account->id,
                        'portfolio_id' => $portfolio->id,
                        'security_id' => $security->id,
                        'user_id' => $user->id,
                    ]);
                }
            }
        }
    }
}
