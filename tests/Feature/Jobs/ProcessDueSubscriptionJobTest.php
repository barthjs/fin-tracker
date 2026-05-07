<?php

declare(strict_types=1);

use App\Enums\CategoryGroup;
use App\Enums\PeriodUnit;
use App\Jobs\ProcessDueSubscriptionJob;
use App\Models\Account;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Services\SubscriptionService;

use function Pest\Laravel\assertDatabaseCount;

beforeEach(fn () => asUser());

/**
 * @param  array<string, mixed>  $attributes
 */
function jobSubscription(array $attributes = []): Subscription
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

it('generates a transaction when handled', function (): void {
    $subscription = jobSubscription();

    new ProcessDueSubscriptionJob($subscription)->handle(resolve(SubscriptionService::class));

    expect(Transaction::query()->where('subscription_id', $subscription->id)->count())->toBe(1);
});

it('does nothing for an inactive subscription', function (): void {
    $subscription = jobSubscription(['is_active' => false]);

    new ProcessDueSubscriptionJob($subscription)->handle(resolve(SubscriptionService::class));

    expect(Transaction::query()->where('subscription_id', $subscription->id)->count())->toBe(0);
});

it('does nothing when auto generation is disabled', function (): void {
    $subscription = jobSubscription(['auto_generate_transaction' => false]);

    new ProcessDueSubscriptionJob($subscription)->handle(resolve(SubscriptionService::class));

    expect(Transaction::query()->where('subscription_id', $subscription->id)->count())->toBe(0);
});

it('notifies the user when the job fails', function (): void {
    $subscription = jobSubscription();

    new ProcessDueSubscriptionJob($subscription)->failed(new Exception('boom'));

    assertDatabaseCount('notifications', 1);
});

it('uses the subscription id as the unique id', function (): void {
    $subscription = jobSubscription();

    expect(new ProcessDueSubscriptionJob($subscription)->uniqueId())->toBe($subscription->id);
});
