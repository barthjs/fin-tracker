<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\TradeType;
use App\Enums\TransactionType;
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

final class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (App::isLocal()) {
            $users = User::all();
            foreach ($users as $user) {
                $this->createTransactions($user);
                $this->createTrades($user);
                break;
            }
        }
    }

    /**
     * Generates demo transaction data for a given user.
     */
    private function createTransactions(User $user): void
    {
        $accounts = Account::factory(3)->create(['is_active' => true, 'user_id' => $user->id]);
        $categories = Category::factory(10)->create(['is_active' => true, 'user_id' => $user->id]);

        for ($m = 11; $m >= 0; $m--) {
            foreach ($accounts as $account) {
                $balance = 0.0;

                for ($i = 0; $i < 2; $i++) {
                    if ($balance <= 0.0) {
                        $type = TransactionType::Revenue;
                        $category = $categories->where('type', TransactionType::Revenue)->random();
                    } else {
                        $type = fake()->randomElement([TransactionType::Expense, TransactionType::Revenue]);
                        $category = $categories->where('type', $type)->random();
                    }

                    $maxAmount = $category->type === TransactionType::Expense ? $balance : 1000;
                    $amount = fake()->randomFloat(2, 0, $maxAmount);
                    $balance += $amount;

                    Transaction::factory()->create([
                        'transaction_date' => Carbon::now()->subMonths($m),
                        'type' => $type,
                        'amount' => $amount,
                        'account_id' => $account->id,
                        'category_id' => $category->id,
                    ]);
                }
            }
        }
    }

    /**
     * Generates demo trades data for a given user.
     */
    private function createTrades(User $user): void
    {
        $portfolios = Portfolio::factory(3)->create(['is_active' => true, 'user_id' => $user->id]);
        Security::factory(10)->create(['is_active' => true, 'user_id' => $user->id]);

        for ($m = 11; $m >= 0; $m--) {
            foreach ($portfolios as $portfolio) {
                for ($i = 0; $i < 2; $i++) {
                    /** @var Security $security */
                    $security = Security::withoutGlobalScopes()->where('user_id', $user->id)->get()->random();
                    /** @var Account $account */
                    $account = Account::withoutGlobalScopes()->where('user_id', $user->id)->get()->random();

                    if ($security->total_quantity <= 0) {
                        $type = TradeType::Buy;
                    } else {
                        $type = fake()->randomElement(TradeType::cases());
                    }

                    $price = fake()->randomFloat(2, 1, 100);

                    if ($type === TradeType::Buy) {
                        if ($account->balance <= 0) {
                            continue;
                        }

                        $maxQuantity = $account->balance / $price;
                        $quantity = fake()->randomFloat(2, 1, $maxQuantity);
                    } else {
                        $quantity = fake()->randomFloat(2, 1, $security->total_quantity);
                    }

                    Trade::factory()->create([
                        'trade_date' => Carbon::now()->subMonths($m),
                        'type' => $type,
                        'quantity' => $quantity,
                        'price' => $price,
                        'account_id' => $account->id,
                        'portfolio_id' => $portfolio->id,
                        'security_id' => $security->id,
                    ]);
                }
            }
        }
    }
}
