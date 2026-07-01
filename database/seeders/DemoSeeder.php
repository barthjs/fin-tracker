<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CategoryGroup;
use App\Enums\PeriodUnit;
use App\Enums\TradeType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\Portfolio;
use App\Models\Security;
use App\Models\User;
use App\Services\BudgetService;
use App\Services\TradeService;
use App\Services\TransactionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;

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
                $this->createBudgets($user);
                $this->createTrades($user);
            }
        }
    }

    /**
     * Generates demo transaction data for a given user.
     */
    private function createTransactions(User $user): void
    {
        $bankNames = __('account.import.examples.name');
        /** @var Collection<int, Account> $accounts */
        $accounts = collect();

        foreach ($bankNames as $name) {
            $accounts->push(Account::factory()->create([
                'name' => $name,
                'user_id' => $user->id,
            ]));
        }

        $demoData = [
            'expense' => [
                'fixed' => [
                    'Rent',
                    'Insurance',
                    'Subscriptions',
                    'Utilities',
                ],
                'variable' => [
                    'Charity',
                    'Dining Out',
                    'Education',
                    'Electronics',
                    'Entertainment',
                    'Gifts',
                    'Groceries',
                    'Healthcare',
                    'Home Improvement',
                    'Pets',
                    'Shopping',
                    'Transportation',
                    'Travel',
                ],
            ],
            'revenue' => [
                'fixed' => [
                    'Salary',
                ],
                'variable' => [
                    'Investments',
                    'Side Income',
                ],
            ],
        ];

        /** @var Collection<int, Category> $categories */
        $categories = collect();

        foreach ($demoData['expense']['fixed'] as $name) {
            $categories->push(Category::factory()->create([
                'name' => $name,
                'group' => CategoryGroup::FixExpenses,
                'user_id' => $user->id,
            ]));
        }

        foreach ($demoData['expense']['variable'] as $name) {
            $categories->push(Category::factory()->create([
                'name' => $name,
                'group' => CategoryGroup::VarExpenses,
                'user_id' => $user->id,
            ]));
        }

        foreach ($demoData['revenue']['fixed'] as $name) {
            $categories->push(Category::factory()->create([
                'name' => $name,
                'group' => CategoryGroup::FixRevenues,
                'user_id' => $user->id,
            ]));
        }

        foreach ($demoData['revenue']['variable'] as $name) {
            $categories->push(Category::factory()->create([
                'name' => $name,
                'group' => CategoryGroup::VarRevenues,
                'user_id' => $user->id,
            ]));
        }

        $transactionService = resolve(TransactionService::class);

        foreach ($accounts as $account) {
            $balance = 0.0;
            for ($m = 11; $m >= 0; $m--) {
                for ($i = 0; $i < 10; $i++) {
                    if ($balance === 0.0) {
                        $type = TransactionType::Revenue;
                        $category = $categories->where('type', TransactionType::Revenue)->random();

                        $amount = 100;
                        $balance += $amount;
                    } elseif ($balance <= 500.0) {
                        $type = TransactionType::Revenue;
                        $category = $categories->where('type', TransactionType::Revenue)->random();

                        $amount = fake()->randomFloat(2, 0, 1000);
                        $balance += $amount;
                    } else {
                        $type = fake()->randomElement([TransactionType::Expense, TransactionType::Revenue]);
                        $category = $categories->where('type', $type)->random();

                        if ($type === TransactionType::Expense) {
                            $amount = fake()->randomFloat(2, 5, $balance / 2);
                            $balance -= $amount;
                        } else {
                            $amount = fake()->randomFloat(2, 50, 1000);
                            $balance += $amount;
                        }
                    }

                    $transactionService->create([
                        'date_time' => Date::now()->subMonths($m)->subDays(fake()->numberBetween(1, 30)),
                        'type' => $type,
                        'amount' => $amount,
                        'payee' => fake()->company(),
                        'account_id' => $account->id,
                        'category_id' => $category->id,
                    ]);
                }
            }
        }
    }

    /**
     * Generates demo budgets for some of the user's expense categories.
     */
    private function createBudgets(User $user): void
    {
        $budgetService = resolve(BudgetService::class);

        $demoBudgets = [
            ['category' => 'Groceries', 'amount' => 400.0, 'period_unit' => PeriodUnit::Month, 'warning_threshold' => 80],
            ['category' => 'Dining Out', 'amount' => 150.0, 'period_unit' => PeriodUnit::Month, 'warning_threshold' => 80],
            ['category' => 'Travel', 'amount' => 2000.0, 'period_unit' => PeriodUnit::Year, 'warning_threshold' => null],
        ];

        foreach ($demoBudgets as $demoBudget) {
            $category = Category::query()
                ->where('user_id', $user->id)
                ->where('name', $demoBudget['category'])
                ->firstOrFail();

            $budgetService->create([
                'category_id' => $category->id,
                'amount' => $demoBudget['amount'],
                'period_unit' => $demoBudget['period_unit'],
                'period_frequency' => 1,
                'starts_at' => $demoBudget['period_unit'] === PeriodUnit::Year
                    ? Date::now()->startOfYear()
                    : Date::now()->startOfMonth(),
                'warning_threshold' => $demoBudget['warning_threshold'],
            ]);
        }
    }

    /**
     * Generates demo trades data for a given user.
     */
    private function createTrades(User $user): void
    {
        $bankNames = __('account.import.examples.name');
        /** @var Collection<int, Portfolio> $portfolios */
        $portfolios = collect();

        foreach ($bankNames as $name) {
            $portfolios->push(Portfolio::factory()->create([
                'name' => $name,
                'user_id' => $user->id,
            ]));
        }

        Security::factory(10)->create(['user_id' => $user->id]);

        $tradeService = resolve(TradeService::class);

        foreach ($portfolios as $portfolio) {
            for ($m = 11; $m >= 0; $m--) {
                for ($i = 0; $i < 2; $i++) {
                    /** @var Security $security */
                    $security = Security::query()->withoutGlobalScopes()->where('user_id', $user->id)->get()->random();
                    /** @var Account $account */
                    $account = Account::query()->withoutGlobalScopes()->where('user_id', $user->id)->get()->random();

                    if ($security->total_quantity <= 0) {
                        $type = TradeType::Buy;
                    } else {
                        $type = fake()->randomElement(TradeType::cases());
                    }

                    $price = fake()->randomFloat(2, 1, 100);

                    if ($type === TradeType::Buy) {
                        if ($account->balance <= 500.0) {
                            continue;
                        }

                        $maxQuantity = $account->balance * 0.5 / $price;
                        $quantity = fake()->randomFloat(2, 1, $maxQuantity);
                    } else {
                        $quantity = fake()->randomFloat(2, 1, $security->total_quantity);
                    }

                    $tradeService->create([
                        'date_time' => Date::now()->subMonths($m),
                        'type' => $type,
                        'quantity' => $quantity,
                        'price' => $price,
                        'tax' => 0.0,
                        'fee' => 0.0,
                        'notes' => fake()->sentence(),
                        'account_id' => $account->id,
                        'portfolio_id' => $portfolio->id,
                        'security_id' => $security->id,
                    ]);
                }
            }
        }
    }
}
