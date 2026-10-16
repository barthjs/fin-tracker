<?php

declare(strict_types=1);

use App\Actions\CheckBudgetThreshold;
use App\Enums\BudgetAlert;
use App\Enums\CategoryGroup;
use App\Enums\NotificationEventType;
use App\Enums\NotificationProviderType;
use App\Enums\PeriodUnit;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\BudgetAlertNotification;
use App\Services\TransactionService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(fn () => asUser());

/**
 * @param  array<string, mixed>  $attributes
 */
function thresholdBudget(array $attributes = []): Budget
{
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    return Budget::factory()->create(array_merge([
        'category_id' => $category->id,
        'amount' => 100.0,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'starts_at' => today()->startOfMonth(),
        'warning_threshold' => 80,
        'notify_on_exceed' => true,
    ], $attributes));
}

function recordExpense(Budget $budget, float $amount): void
{
    Transaction::factory()->create([
        'account_id' => Account::factory()->create()->id,
        'category_id' => $budget->category_id,
        'type' => TransactionType::Expense,
        'amount' => $amount,
        'date_time' => now(),
    ]);
}

function assignBudgetTarget(Budget $budget): NotificationTarget
{
    $target = NotificationTarget::factory()->create(['type' => NotificationProviderType::DATABASE]);

    $budget->syncNotificationAssignments(NotificationEventType::BUDGET_ALERT, [$target->id]);

    return $target;
}

function runThresholdCheck(Budget $budget): void
{
    resolve(CheckBudgetThreshold::class)($budget->category_id);
}

/**
 * Simulates a concurrent save that sends the exceeded alert while the check calculates the usage.
 */
function claimExceededAlertDuringCalculation(Budget $budget): void
{
    $claimed = false;

    DB::listen(function (QueryExecuted $query) use ($budget, &$claimed): void {
        if ($claimed || ! str_contains($query->sql, 'sum(')) {
            return;
        }

        $claimed = true;

        DB::table('budgets')->where('id', $budget->id)->update(['last_exceeded_at' => now()]);
    });
}

function budgetOwner(Budget $budget): User
{
    return new User(['id' => $budget->user->id]);
}

it('sends an exceeded alert and stamps last_exceeded_at when the budget is used up', function (): void {
    Notification::fake();

    $budget = thresholdBudget();
    assignBudgetTarget($budget);
    recordExpense($budget, 100.0);

    runThresholdCheck($budget);

    Notification::assertSentTo(
        budgetOwner($budget),
        BudgetAlertNotification::class,
        fn (BudgetAlertNotification $notification): bool => $notification->toNotificationPayload()->metadata['alert'] === BudgetAlert::Exceeded->value
    );
    expect($budget->fresh()?->last_exceeded_at)->not->toBeNull();
});

it('sends a warning alert and stamps last_warned_at when the warning threshold is reached', function (): void {
    Notification::fake();

    $budget = thresholdBudget();
    assignBudgetTarget($budget);
    recordExpense($budget, 85.0);

    runThresholdCheck($budget);

    Notification::assertSentTo(
        budgetOwner($budget),
        BudgetAlertNotification::class,
        fn (BudgetAlertNotification $notification): bool => $notification->toNotificationPayload()->metadata['alert'] === BudgetAlert::Warning->value
    );
    expect($budget->fresh()?->last_warned_at)->not->toBeNull()
        ->and($budget->fresh()?->last_exceeded_at)->toBeNull();
});

it('does not notify when spending is below the warning threshold', function (): void {
    Notification::fake();

    $budget = thresholdBudget();
    assignBudgetTarget($budget);
    recordExpense($budget, 50.0);

    runThresholdCheck($budget);

    Notification::assertNothingSent();
});

it('does not warn when the budget has no warning threshold', function (): void {
    Notification::fake();

    $budget = thresholdBudget(['warning_threshold' => null]);
    assignBudgetTarget($budget);
    recordExpense($budget, 95.0);

    runThresholdCheck($budget);

    Notification::assertNothingSent();
});

it('sends the warning and the exceeded alert once each within the same period', function (): void {
    Notification::fake();

    $budget = thresholdBudget();
    assignBudgetTarget($budget);

    recordExpense($budget, 85.0);
    runThresholdCheck($budget);
    recordExpense($budget, 5.0);
    runThresholdCheck($budget);
    recordExpense($budget, 20.0);
    runThresholdCheck($budget);
    recordExpense($budget, 20.0);
    runThresholdCheck($budget);

    Notification::assertSentToTimes(budgetOwner($budget), BudgetAlertNotification::class, 2);
});

it('does not warn after the budget was already exceeded in the same period', function (): void {
    Notification::fake();

    $budget = thresholdBudget();
    assignBudgetTarget($budget);
    recordExpense($budget, 85.0);
    $budget->updateQuietly(['last_exceeded_at' => now()]);

    runThresholdCheck($budget);

    Notification::assertNothingSent();
});

it('sends the exceeded alert again in a new period', function (): void {
    Notification::fake();

    $budget = thresholdBudget();
    assignBudgetTarget($budget);
    recordExpense($budget, 100.0);
    $budget->updateQuietly(['last_exceeded_at' => today()->startOfMonth()->subDay()]);

    runThresholdCheck($budget);

    Notification::assertSentTo(budgetOwner($budget), BudgetAlertNotification::class);
});

it('does nothing for a category without budget', function (): void {
    Notification::fake();

    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    resolve(CheckBudgetThreshold::class)($category->id);

    Notification::assertNothingSent();
});

it('does not notify when another save claimed the alert in the meantime', function (): void {
    Notification::fake();

    $budget = thresholdBudget();
    assignBudgetTarget($budget);
    recordExpense($budget, 100.0);

    claimExceededAlertDuringCalculation($budget);

    runThresholdCheck($budget);

    Notification::assertNothingSent();
});

it('does nothing for an inactive budget', function (): void {
    Notification::fake();

    $budget = thresholdBudget(['is_active' => false]);
    assignBudgetTarget($budget);
    recordExpense($budget, 200.0);

    runThresholdCheck($budget);

    Notification::assertNothingSent();
});

it('does nothing when notifications are disabled', function (): void {
    Notification::fake();

    $budget = thresholdBudget(['notify_on_exceed' => false]);
    assignBudgetTarget($budget);
    recordExpense($budget, 200.0);

    runThresholdCheck($budget);

    Notification::assertNothingSent();
});

it('marks the alert as sent without sending when no targets are assigned', function (): void {
    Notification::fake();

    $budget = thresholdBudget();
    recordExpense($budget, 150.0);

    runThresholdCheck($budget);

    Notification::assertNothingSent();
    expect($budget->fresh()?->last_exceeded_at)->not->toBeNull();
});

it('writes a database notification with the category name through the dynamic channel', function (): void {
    $budget = thresholdBudget();
    assignBudgetTarget($budget);
    recordExpense($budget, 120.0);

    runThresholdCheck($budget);

    $notification = DB::table('notifications')->sole();

    expect($notification->data)->toContain($budget->category->name);
});

it('rolls the alert back together with a failed booking', function (): void {
    $budget = thresholdBudget();
    assignBudgetTarget($budget);

    expect(fn () => DB::transaction(function () use ($budget): void {
        resolve(TransactionService::class)->create([
            'account_id' => Account::factory()->create()->id,
            'category_id' => $budget->category_id,
            'type' => TransactionType::Expense,
            'amount' => 120.0,
            'date_time' => now(),
        ]);

        throw new RuntimeException('Booking failed');
    }))->toThrow(RuntimeException::class)
        ->and($budget->fresh()?->last_exceeded_at)->toBeNull()
        ->and(DB::table('notifications')->count())->toBe(0);
});
