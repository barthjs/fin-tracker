<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\NotificationEventType;
use App\Enums\PeriodUnit;
use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\Transaction;
use App\Services\BudgetService;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

function budgetService(): BudgetService
{
    return resolve(BudgetService::class);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function budgetData(array $overrides = []): array
{
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    return array_merge([
        'category_id' => $category->id,
        'amount' => 400.0,
        'period_unit' => PeriodUnit::Month->value,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth()->toDateString(),
        'notify_on_exceed' => true,
        'warning_threshold' => 90,
    ], $overrides);
}

it('creates a budget', function (): void {
    $budget = budgetService()->create(budgetData());

    assertDatabaseHas('budgets', [
        'id' => $budget->id,
        'amount' => 400.0,
    ]);
});

it('syncs notification targets on create', function (): void {
    $target = NotificationTarget::factory()->create();

    $budget = budgetService()->create(budgetData(['exceed_targets' => [$target->id]]));

    expect($budget->getNotificationTargetsForEvent(NotificationEventType::BUDGET_ALERT)->pluck('id'))
        ->toContain($target->id);
});

it('re-syncs notification targets on update', function (): void {
    $targetA = NotificationTarget::factory()->create();
    $targetB = NotificationTarget::factory()->create();

    $budget = budgetService()->create(budgetData(['exceed_targets' => [$targetA->id]]));

    budgetService()->update($budget, ['amount' => 500.0, 'exceed_targets' => [$targetB->id]]);

    assertDatabaseHas('budgets', ['id' => $budget->id, 'amount' => 500.0]);

    $ids = $budget->getNotificationTargetsForEvent(NotificationEventType::BUDGET_ALERT)->pluck('id');

    expect($ids)->toContain($targetB->id)
        ->and($ids)->not->toContain($targetA->id);
});

it('keeps notification targets when the update data contains none', function (): void {
    $target = NotificationTarget::factory()->create();

    $budget = budgetService()->create(budgetData(['exceed_targets' => [$target->id]]));

    budgetService()->update($budget, ['amount' => 500.0]);

    expect($budget->getNotificationTargetsForEvent(NotificationEventType::BUDGET_ALERT)->pluck('id'))
        ->toContain($target->id);
});

it('clears notification targets when an empty list is provided on update', function (): void {
    $target = NotificationTarget::factory()->create();

    $budget = budgetService()->create(budgetData(['exceed_targets' => [$target->id]]));

    budgetService()->update($budget, ['exceed_targets' => []]);

    expect($budget->getNotificationTargetsForEvent(NotificationEventType::BUDGET_ALERT))->toBeEmpty();
});

it('checks the usage after create', function (): void {
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    Transaction::factory()->create(['category_id' => $category->id, 'type' => TransactionType::Expense, 'amount' => 500.0, 'date_time' => now()]);

    $budget = budgetService()->create(budgetData(['category_id' => $category->id, 'notify_on_exceed' => true]));

    expect($budget->fresh()?->last_exceeded_at)->not->toBeNull();
});

it('checks the usage after update', function (): void {
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);
    Transaction::factory()->create(['category_id' => $category->id, 'type' => TransactionType::Expense, 'amount' => 500.0, 'date_time' => now()]);

    $budget = budgetService()->create(budgetData(['category_id' => $category->id, 'notify_on_exceed' => false]));

    budgetService()->update($budget, ['notify_on_exceed' => true]);

    expect($budget->fresh()?->last_exceeded_at)->not->toBeNull();
});

it('allows the alerts of the current period again when what the budget measures changes', function (array $change): void {
    $budget = budgetService()->create(budgetData());
    $budget->updateQuietly(['last_warned_at' => now(), 'last_exceeded_at' => now()]);

    budgetService()->update($budget, $change);

    expect($budget->fresh()?->last_warned_at)->toBeNull()
        ->and($budget->fresh()?->last_exceeded_at)->toBeNull();
})->with([
    'category' => fn (): array => [
        'category_id' => Category::factory()->create(['group' => CategoryGroup::VarExpenses])->id,
    ],
    'amount' => fn (): array => ['amount' => 600.0],
    'warning threshold' => fn (): array => ['warning_threshold' => 70],
    'period unit' => fn (): array => ['period_unit' => PeriodUnit::Year->value],
    'period frequency' => fn (): array => ['period_frequency' => 2],
    'start date' => fn (): array => ['starts_at' => today()->startOfYear()->toDateString()],
]);

it('keeps the sent alerts when only the end date changes', function (): void {
    $budget = budgetService()->create(budgetData());
    $budget->updateQuietly(['last_warned_at' => now(), 'last_exceeded_at' => now()]);

    budgetService()->update($budget, ['ends_at' => today()->addYear()->toDateString()]);

    expect($budget->fresh()?->last_warned_at)->not->toBeNull()
        ->and($budget->fresh()?->last_exceeded_at)->not->toBeNull();
});
