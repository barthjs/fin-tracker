<?php

declare(strict_types=1);

use App\Enums\NotificationEventType;
use App\Models\Account;
use App\Models\Category;
use App\Models\NotificationAssignment;
use App\Models\NotificationTarget;
use App\Models\Subscription;

beforeEach(fn () => asUser());

it('resolves its target, notifiable and event type', function (): void {
    $target = NotificationTarget::factory()->create();
    $account = Account::factory()->create();
    $category = Category::factory()->create();
    $subscription = Subscription::factory()->create([
        'account_id' => $account->id,
        'category_id' => $category->id,
    ]);

    $subscription->syncNotificationAssignments(NotificationEventType::SUBSCRIPTION_REMINDER, [$target->id]);

    $assignment = NotificationAssignment::query()->firstOrFail();

    expect($assignment->target->id)->toBe($target->id)
        ->and($assignment->notifiable->getKey())->toBe($subscription->id)
        ->and($assignment->event_type)->toBe(NotificationEventType::SUBSCRIPTION_REMINDER);
});
