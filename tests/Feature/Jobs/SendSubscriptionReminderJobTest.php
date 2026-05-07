<?php

declare(strict_types=1);

use App\Enums\NotificationEventType;
use App\Enums\NotificationProviderType;
use App\Jobs\SendSubscriptionReminderJob;
use App\Models\Account;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionReminderNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(fn () => asUser());

/**
 * @param  array<string, mixed>  $attributes
 */
function reminderSubscription(array $attributes = []): Subscription
{
    $account = Account::factory()->create();
    $category = Category::factory()->create();

    return Subscription::factory()->create(array_merge([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'remind_before_payment' => true,
        'is_active' => true,
        'next_payment_date' => today()->addDays(2),
    ], $attributes))->load(['user', 'account']);
}

it('notifies every assigned target and marks the subscription as reminded', function (): void {
    Notification::fake();

    $target = NotificationTarget::factory()->create(['type' => NotificationProviderType::DATABASE]);
    $subscription = reminderSubscription();
    $subscription->syncNotificationAssignments(NotificationEventType::SUBSCRIPTION_REMINDER, [$target->id]);

    new SendSubscriptionReminderJob($subscription)->handle();

    Notification::assertSentTo(
        new User(['id' => $subscription->user->id]),
        SubscriptionReminderNotification::class
    );
    expect($subscription->fresh()?->last_reminded_at)->not->toBeNull();
});

it('marks as reminded without notifying when there are no targets', function (): void {
    Notification::fake();

    $subscription = reminderSubscription();

    new SendSubscriptionReminderJob($subscription)->handle();

    Notification::assertNothingSent();
    expect($subscription->fresh()?->last_reminded_at)->not->toBeNull();
});

it('does nothing for an inactive subscription', function (): void {
    Notification::fake();

    $subscription = reminderSubscription(['is_active' => false]);

    new SendSubscriptionReminderJob($subscription)->handle();

    Notification::assertNothingSent();
    expect($subscription->fresh()?->last_reminded_at)->toBeNull();
});

it('uses the subscription id as the unique id', function (): void {
    $subscription = reminderSubscription();

    expect(new SendSubscriptionReminderJob($subscription)->uniqueId())->toBe($subscription->id);
});
