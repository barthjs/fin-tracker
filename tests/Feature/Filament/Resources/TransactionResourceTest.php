<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Filament\Actions\Testing\TestAction;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertModelMissing;
use function Pest\Livewire\livewire;

beforeEach(fn () => asUser());

it('renders the list page', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $transactions = Transaction::factory()
        ->count(3)
        ->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'date_time' => now(),
        ]);

    livewire(ListTransactions::class)
        ->assertOk()
        ->assertCanSeeTableRecords($transactions)
        ->assertActionExists('import')
        ->assertActionExists('export');
});

it('scopes transactions to the current user and filters by account, category, date and type', function (): void {
    $accountA = Account::factory()->create();
    $accountB = Account::factory()->create();
    $expenseCategory = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    $revenueCategory = Category::factory()->create(['group' => CategoryGroup::VarRevenues]);

    $expense = Transaction::factory()->create([
        'type' => TransactionType::Expense,
        'date_time' => now()->subDays(2),
        'account_id' => $accountA->id,
        'category_id' => $expenseCategory->id,
    ]);
    $revenue = Transaction::factory()->create([
        'type' => TransactionType::Revenue,
        'date_time' => now()->subDays(10),
        'account_id' => $accountB->id,
        'category_id' => $revenueCategory->id,
    ]);

    $other = User::factory()->create();
    $otherTransaction = Transaction::factory()->create([
        'account_id' => Account::factory()->create(['user_id' => $other->id])->id,
        'category_id' => Category::factory()->create(['user_id' => $other->id])->id,
    ]);

    livewire(ListTransactions::class)
        ->assertCanSeeTableRecords([$expense, $revenue])
        ->assertCanNotSeeTableRecords([$otherTransaction])
        ->filterTable('account_id', $accountA)
        ->assertCanSeeTableRecords([$expense])
        ->assertCanNotSeeTableRecords([$revenue])
        ->resetTableFilters()
        ->filterTable('category_id', $revenueCategory)
        ->assertCanSeeTableRecords([$revenue])
        ->assertCanNotSeeTableRecords([$expense])
        ->resetTableFilters()
        ->filterTable('date_range', ['from' => now()->subDays(5)->toDateString(), 'until' => now()->toDateString()])
        ->assertCanSeeTableRecords([$expense])
        ->assertCanNotSeeTableRecords([$revenue])
        ->resetTableFilters()
        ->set('activeTab', TransactionType::Expense->value)
        ->assertCanSeeTableRecords([$expense])
        ->assertCanNotSeeTableRecords([$revenue])
        ->set('activeTab', TransactionType::Revenue->value)
        ->assertCanSeeTableRecords([$revenue])
        ->assertCanNotSeeTableRecords([$expense])
        ->set('activeTab', CategoryGroup::VarExpenses->value)
        ->assertCanSeeTableRecords([$expense])
        ->assertCanNotSeeTableRecords([$revenue]);
});

it('can create a transaction', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $data = Transaction::factory()->make([
        'account_id' => $account->id,
        'category_id' => $category->id,
    ])->toArray();
    $data['date_time'] = now()->startOfMinute()->toDateTimeString();

    livewire(ListTransactions::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('transactions', $data);
});

it('can edit a transaction', function (): void {
    $transaction = Transaction::factory()->create();

    $data = Transaction::factory()
        ->make([
            'account_id' => $transaction->account_id,
            'category_id' => $transaction->category_id,
        ])
        ->toArray();
    $data['date_time'] = now()->startOfMinute()->toDateTimeString();

    livewire(ListTransactions::class)
        ->callAction(
            TestAction::make('edit')->table($transaction),
            $data
        )
        ->assertHasNoFormErrors();

    assertDatabaseHas('transactions', array_merge(['id' => $transaction->id], $data));
});

it('can bulk edit the account of transactions', function (): void {
    $accountA = Account::factory()->create();
    $accountB = Account::factory()->create();
    $category = Category::factory()->create();

    $transactions = Transaction::factory()->count(2)->create([
        'account_id' => $accountA->id,
        'category_id' => $category->id,
    ]);

    livewire(ListTransactions::class)
        ->callTableBulkAction('account_id', $transactions, ['account_id' => $accountB->id]);

    foreach ($transactions as $transaction) {
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'account_id' => $accountB->id]);
    }
});

it('can delete a transaction', function (): void {
    $transaction = Transaction::factory()->create();

    livewire(ListTransactions::class)
        ->callAction(TestAction::make('delete')->table($transaction));

    assertModelMissing($transaction);
});

it('can bulk delete transactions', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    $transactions = Transaction::factory()->count(3)->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    livewire(ListTransactions::class)
        ->callTableBulkAction('delete', $transactions);

    foreach ($transactions as $transaction) {
        $this->assertModelMissing($transaction);
    }
});

it('filters transactions by category group tabs', function (): void {
    $account = Account::factory()->create();
    $fixExpenseCategory = Category::factory()->create(['group' => CategoryGroup::FixExpenses]);
    $varRevenueCategory = Category::factory()->create(['group' => CategoryGroup::VarRevenues]);
    $fixRevenueCategory = Category::factory()->create(['group' => CategoryGroup::FixRevenues]);

    $fixExpense = Transaction::factory()->create([
        'account_id' => $account->id,
        'category_id' => $fixExpenseCategory->id,
        'type' => TransactionType::Expense,
    ]);
    $varRevenue = Transaction::factory()->create([
        'account_id' => $account->id,
        'category_id' => $varRevenueCategory->id,
        'type' => TransactionType::Revenue,
    ]);
    $fixRevenue = Transaction::factory()->create([
        'account_id' => $account->id,
        'category_id' => $fixRevenueCategory->id,
        'type' => TransactionType::Revenue,
    ]);

    livewire(ListTransactions::class)
        ->set('activeTab', CategoryGroup::FixExpenses->value)
        ->assertCanSeeTableRecords([$fixExpense])
        ->assertCanNotSeeTableRecords([$varRevenue, $fixRevenue])
        ->set('activeTab', CategoryGroup::VarRevenues->value)
        ->assertCanSeeTableRecords([$varRevenue])
        ->assertCanNotSeeTableRecords([$fixExpense, $fixRevenue])
        ->set('activeTab', CategoryGroup::FixRevenues->value)
        ->assertCanSeeTableRecords([$fixRevenue])
        ->assertCanNotSeeTableRecords([$fixExpense, $varRevenue]);
});

it('recalculates the account balance when creating a transaction', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    $data = [
        'date_time' => now()->startOfMinute()->toDateTimeString(),
        'type' => TransactionType::Expense->value,
        'amount' => 100,
        'payee' => 'Shop',
        'account_id' => $account->id,
        'category_id' => $category->id,
    ];

    livewire(ListTransactions::class)
        ->callAction('create', $data)
        ->assertHasNoFormErrors();

    expect($account->fresh()?->balance)->toBe(-100.0);
});
