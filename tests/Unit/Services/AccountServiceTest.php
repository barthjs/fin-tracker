<?php

declare(strict_types=1);

use App\Enums\NotificationEventType;
use App\Enums\NotificationProviderType;
use App\Models\Account;
use App\Models\NotificationTarget;
use App\Notifications\AccountLowBalanceNotification;
use App\Services\AccountService;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(fn () => asUser());

function accountService(): AccountService
{
    return resolve(AccountService::class);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function accountData(array $overrides = []): array
{
    return array_merge(
        Account::factory()->make()->only(['name', 'currency', 'color']),
        $overrides
    );
}

it('creates an account', function (): void {
    $data = accountData();

    $account = accountService()->create($data);

    assertDatabaseHas('accounts', [
        'id' => $account->id,
        'name' => $data['name'],
    ]);
});

it('syncs notification targets on create', function (): void {
    $target = NotificationTarget::factory()->create();

    $account = accountService()->create(accountData(['low_balance_targets' => [$target->id]]));

    expect($account->getNotificationTargetsForEvent(NotificationEventType::ACCOUNT_LOW_BALANCE)->pluck('id'))
        ->toContain($target->id);
});

it('re-syncs notification targets on update', function (): void {
    $targetA = NotificationTarget::factory()->create();
    $targetB = NotificationTarget::factory()->create();

    $account = accountService()->create(accountData(['low_balance_targets' => [$targetA->id]]));

    $name = Account::factory()->make()->name;

    accountService()->update($account, ['name' => $name, 'low_balance_targets' => [$targetB->id]]);

    assertDatabaseHas('accounts', ['id' => $account->id, 'name' => $name]);

    $ids = $account->getNotificationTargetsForEvent(NotificationEventType::ACCOUNT_LOW_BALANCE)->pluck('id');

    expect($ids)->toContain($targetB->id)
        ->and($ids)->not->toContain($targetA->id);
});

it('keeps notification targets when the update data contains none', function (): void {
    $target = NotificationTarget::factory()->create();

    $account = accountService()->create(accountData(['low_balance_targets' => [$target->id]]));

    accountService()->update($account, ['name' => Account::factory()->make()->name]);

    expect($account->getNotificationTargetsForEvent(NotificationEventType::ACCOUNT_LOW_BALANCE)->pluck('id'))
        ->toContain($target->id);
});

it('clears notification targets when an empty list is provided on update', function (): void {
    $target = NotificationTarget::factory()->create();

    $account = accountService()->create(accountData(['low_balance_targets' => [$target->id]]));

    accountService()->update($account, ['low_balance_targets' => []]);

    expect($account->getNotificationTargetsForEvent(NotificationEventType::ACCOUNT_LOW_BALANCE))->toBeEmpty();
});

it('checks the balance after create', function (): void {
    $account = accountService()->create(accountData(['notify_on_low_balance' => true, 'low_balance_threshold' => 100.0]));

    expect($account->fresh()?->low_balance_notified_at)->not->toBeNull();
});

it('checks the balance after update', function (): void {
    $account = accountService()->create(accountData(['low_balance_threshold' => 100.0]));

    accountService()->update($account, ['notify_on_low_balance' => true]);

    expect($account->fresh()?->low_balance_notified_at)->not->toBeNull();
});

it('sends the low balance alert again when the threshold changes', function (): void {
    Notification::fake();

    $target = NotificationTarget::factory()->create(['type' => NotificationProviderType::DATABASE]);
    $account = accountService()->create(accountData([
        'notify_on_low_balance' => true,
        'low_balance_threshold' => 100.0,
        'low_balance_targets' => [$target->id],
    ]));

    accountService()->update($account, ['low_balance_threshold' => 200.0]);

    Notification::assertSentTimes(AccountLowBalanceNotification::class, 2);
});

it('keeps the sent low balance alert when the threshold stays the same', function (): void {
    $account = accountService()->create(accountData(['notify_on_low_balance' => true, 'low_balance_threshold' => 100.0]));
    $account->updateQuietly(['low_balance_notified_at' => now()]);

    accountService()->update($account, ['name' => Account::factory()->make()->name]);

    expect($account->fresh()?->low_balance_notified_at)->not->toBeNull();
});

it('sends the low balance alert again when the notification is switched on again', function (): void {
    Notification::fake();

    $target = NotificationTarget::factory()->create(['type' => NotificationProviderType::DATABASE]);
    $account = accountService()->create(accountData([
        'notify_on_low_balance' => true,
        'low_balance_threshold' => 100.0,
        'low_balance_targets' => [$target->id],
    ]));

    accountService()->update($account, ['notify_on_low_balance' => false]);
    accountService()->update($account, ['notify_on_low_balance' => true]);

    Notification::assertSentTimes(AccountLowBalanceNotification::class, 2);
});
