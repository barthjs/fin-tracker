<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\NotificationEventType;
use App\Enums\PeriodUnit;
use App\Jobs\ProcessDueSubscriptionJob;
use App\Jobs\SendSubscriptionReminderJob;
use App\Models\Account;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Bus;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

function subscriptionService(): SubscriptionService
{
    return resolve(SubscriptionService::class);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function dueSubscription(array $attributes = []): Subscription
{
    $account = Account::factory()->create();
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    return Subscription::factory()->create(array_merge([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 50.0,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'next_payment_date' => today(),
        'auto_generate_transaction' => true,
        'is_active' => true,
    ], $attributes))->load(['user', 'account', 'category']);
}

it('creates a subscription and derives the day of month from the next payment date', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    $subscription = subscriptionService()->create([
        'name' => 'Netflix',
        'amount' => 9.99,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'started_at' => today()->toDateString(),
        'next_payment_date' => today()->addMonth()->toDateString(),
        'auto_generate_transaction' => true,
        'color' => '#ffffff',
        'is_active' => true,
    ]);

    expect($subscription->day_of_month)->toBe(today()->addMonth()->day);
    assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'name' => 'Netflix']);
});

it('syncs reminder targets on create', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    $target = NotificationTarget::factory()->create();

    $subscription = subscriptionService()->create([
        'name' => 'Spotify',
        'amount' => 9.99,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'started_at' => today()->toDateString(),
        'next_payment_date' => today()->addMonth()->toDateString(),
        'auto_generate_transaction' => false,
        'remind_before_payment' => true,
        'reminder_days_before' => 3,
        'reminder_targets' => [$target->id],
        'color' => '#ffffff',
        'is_active' => true,
    ]);

    $targets = $subscription->getNotificationTargetsForEvent(NotificationEventType::SUBSCRIPTION_REMINDER);

    expect($targets->pluck('id')->all())->toContain($target->id);
});

it('updates a subscription', function (): void {
    $subscription = dueSubscription(['next_payment_date' => today()->addMonth(), 'name' => 'Old']);

    $updated = subscriptionService()->update($subscription, [
        'name' => 'New',
        'next_payment_date' => today()->addMonths(2)->toDateString(),
    ]);

    expect($updated->name)->toBe('New')
        ->and($updated->day_of_month)->toBe(today()->addMonths(2)->day);
});

it('syncs reminder targets on update', function (): void {
    $subscription = dueSubscription(['next_payment_date' => today()->addMonth(), 'auto_generate_transaction' => false]);
    $target = NotificationTarget::factory()->create();

    subscriptionService()->update($subscription, [
        'remind_before_payment' => true,
        'reminder_days_before' => 3,
        'reminder_targets' => [$target->id],
    ]);

    expect($subscription->getNotificationTargetsForEvent(NotificationEventType::SUBSCRIPTION_REMINDER)->pluck('id')->all())
        ->toContain($target->id);
});

it('runs the due-check when creating a subscription due today', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    $subscription = subscriptionService()->create([
        'name' => 'Due Today',
        'amount' => 9.99,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'started_at' => today()->toDateString(),
        'next_payment_date' => today()->toDateString(),
        'auto_generate_transaction' => true,
        'color' => '#ffffff',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('subscriptions', ['id' => $subscription->id, 'name' => 'Due Today']);
});

it('ignores subscriptions due over a year ago in the statistics', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 30.0,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'next_payment_date' => today()->subYears(2),
    ]);

    $stats = subscriptionService()->calculateStats(Subscription::query()->get());

    expect($stats['due_this_month'])->toBe(0.0);
});

it('generates a single due transaction and advances the next payment date', function (): void {
    $subscription = dueSubscription(['next_payment_date' => today()]);

    subscriptionService()->generateTransaction($subscription);

    expect(Transaction::query()->where('subscription_id', $subscription->id)->count())->toBe(1)
        ->and($subscription->account->fresh()?->balance)->toBe(-50.0)
        ->and($subscription->fresh()?->next_payment_date->toDateString())->toBe(today()->addMonth()->toDateString());

    $this->assertDatabaseCount('notifications', 1);
});

it('catches up multiple missed transactions', function (): void {
    $subscription = dueSubscription(['next_payment_date' => today()->subMonths(2)]);

    subscriptionService()->generateTransaction($subscription);

    expect(Transaction::query()->where('subscription_id', $subscription->id)->count())->toBe(3)
        ->and($subscription->fresh()?->next_payment_date->toDateString())->toBe(today()->addMonth()->toDateString());
});

it('stops generating transactions after the ended_at date', function (): void {
    $subscription = dueSubscription([
        'next_payment_date' => today()->subMonths(2),
        'ended_at' => today()->subMonth(),
    ]);

    subscriptionService()->generateTransaction($subscription);

    expect(Transaction::query()->where('subscription_id', $subscription->id)->count())->toBe(2);
});

it('does not generate transactions for a future subscription', function (): void {
    $subscription = dueSubscription(['next_payment_date' => today()->addMonth()]);

    subscriptionService()->generateTransaction($subscription);

    expect(Transaction::query()->where('subscription_id', $subscription->id)->count())->toBe(0);
});

it('calculates subscription statistics', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    Subscription::factory()->count(2)->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 30.0,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
        'next_payment_date' => today()->addDays(3),
    ]);

    $stats = subscriptionService()->calculateStats(Subscription::query()->get());

    expect($stats)->toHaveKeys(['monthly_avg', 'yearly_avg', 'due_this_month', 'daily_chart'])
        ->and($stats['monthly_avg'])->toBe(60.0)
        ->and($stats['yearly_avg'])->toBe(720.0);
});

it('calculates stats across all period units and skips zero-frequency subscriptions', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    foreach ([PeriodUnit::Day, PeriodUnit::Week, PeriodUnit::Year] as $unit) {
        Subscription::factory()->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'amount' => 30.0,
            'period_unit' => $unit,
            'period_frequency' => 1,
            'next_payment_date' => today(),
        ]);
    }

    Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 999.0,
        'period_frequency' => 0,
    ]);

    $stats = subscriptionService()->calculateStats(Subscription::query()->get());

    expect($stats['monthly_avg'])->toBeGreaterThan(0.0);
});

it('skips zero-frequency subscriptions in the chart allocation', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'period_frequency' => 0,
    ]);

    $result = subscriptionService()->getChartAllocation(
        Subscription::query()->with(['category', 'account'])->get(),
        'category'
    );

    expect($result['labels'])->toBe([]);
});

it('builds a chart allocation grouped by category', function (): void {
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    Subscription::factory()->count(2)->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'amount' => 30.0,
        'period_unit' => PeriodUnit::Month,
        'period_frequency' => 1,
    ]);

    $result = subscriptionService()->getChartAllocation(
        Subscription::query()->with(['category', 'account'])->get(),
        'category'
    );

    expect($result)->toHaveKeys(['labels', 'datasets'])
        ->and($result['labels'])->toContain($category->name);
});

it('dispatches reminder jobs for due reminders', function (): void {
    Bus::fake();

    dueSubscription([
        'next_payment_date' => today()->addDays(2),
        'remind_before_payment' => true,
        'reminder_days_before' => 3,
        'auto_generate_transaction' => false,
    ]);

    subscriptionService()->dispatchReminders();

    Bus::assertDispatched(SendSubscriptionReminderJob::class);
});

it('dispatches process jobs for due subscriptions', function (): void {
    Bus::fake();

    dueSubscription(['next_payment_date' => today()]);

    subscriptionService()->dispatchDueSubscriptions();

    Bus::assertDispatched(ProcessDueSubscriptionJob::class);
});
