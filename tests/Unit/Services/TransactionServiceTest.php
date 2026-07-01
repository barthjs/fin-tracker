<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Services\TransactionService;

beforeEach(fn () => asUser());

it('no-ops the transaction service bulk methods on empty collections', function (): void {
    $service = resolve(TransactionService::class);

    $service->bulkEditAccount(collect(), ['account_id' => 'missing']);
    $service->bulkDelete(collect());

    expect(true)->toBeTrue();
});

it('checks the account balance when a transaction recalculates it', function (): void {
    $account = Account::factory()->create(['notify_on_low_balance' => true, 'low_balance_threshold' => 100.0]);
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    resolve(TransactionService::class)->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'type' => TransactionType::Expense,
        'amount' => 50.0,
        'date_time' => now(),
    ]);

    expect($account->fresh()?->low_balance_notified_at)->not->toBeNull();
});

it('checks the budget when a transaction is created in a budgeted category', function (): void {
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    $budget = Budget::factory()->notifying()->create(['category_id' => $category->id, 'amount' => 100.0]);

    resolve(TransactionService::class)->create([
        'account_id' => Account::factory()->create()->id,
        'category_id' => $budget->category_id,
        'type' => TransactionType::Expense,
        'amount' => 120.0,
        'date_time' => now(),
    ]);

    expect($budget->fresh()?->last_exceeded_at)->not->toBeNull();
});
