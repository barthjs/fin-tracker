<?php

declare(strict_types=1);

use App\Actions\CheckAccountLowBalance;
use App\Enums\CategoryGroup;
use App\Enums\NotificationEventType;
use App\Enums\NotificationProviderType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\NotificationTarget;
use App\Models\User;
use App\Notifications\AccountLowBalanceNotification;
use App\Services\TransactionService;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

beforeEach(fn () => asUser());

/**
 * @param  array<string, mixed>  $attributes
 */
function lowBalanceAccount(array $attributes = []): Account
{
    return Account::factory()->create(array_merge([
        'notify_on_low_balance' => true,
        'low_balance_threshold' => 100.0,
    ], $attributes));
}

function assignAccountTarget(Account $account): NotificationTarget
{
    $target = NotificationTarget::factory()->create(['type' => NotificationProviderType::DATABASE]);

    $account->syncNotificationAssignments(NotificationEventType::ACCOUNT_LOW_BALANCE, [$target->id]);

    return $target;
}

function runLowBalanceCheck(Account $account): void
{
    resolve(CheckAccountLowBalance::class)($account->id);
}

/**
 * Simulates a concurrent save that sends the alert right after the check has loaded the account.
 */
function claimLowBalanceAlertAfterLookup(Account $account): void
{
    $claimed = false;

    DB::listen(function (QueryExecuted $query) use ($account, &$claimed): void {
        if ($claimed || ! str_starts_with($query->sql, 'select') || ! str_contains($query->sql, '"accounts"')) {
            return;
        }

        $claimed = true;

        DB::table('accounts')->where('id', $account->id)->update(['low_balance_notified_at' => now()]);
    });
}

function accountOwner(Account $account): User
{
    return new User(['id' => $account->user->id]);
}

it('notifies assigned targets and stamps low_balance_notified_at when the balance drops below the threshold', function (): void {
    Notification::fake();

    $account = lowBalanceAccount(['balance' => 50.0]);
    assignAccountTarget($account);

    runLowBalanceCheck($account);

    Notification::assertSentTo(accountOwner($account), AccountLowBalanceNotification::class);
    expect($account->fresh()?->low_balance_notified_at)->not->toBeNull();
});

it('does not notify while the balance stays at or above the threshold', function (): void {
    Notification::fake();

    $account = lowBalanceAccount(['balance' => 150.0]);
    assignAccountTarget($account);

    runLowBalanceCheck($account);

    Notification::assertNothingSent();
    expect($account->fresh()?->low_balance_notified_at)->toBeNull();
});

it('only notifies once while the balance stays below the threshold', function (): void {
    Notification::fake();

    $account = lowBalanceAccount(['balance' => 50.0]);
    assignAccountTarget($account);

    runLowBalanceCheck($account);
    runLowBalanceCheck($account);

    Notification::assertSentToTimes(accountOwner($account), AccountLowBalanceNotification::class, 1);
});

it('re-arms and notifies again after the balance recovers and dips again', function (): void {
    Notification::fake();

    $account = lowBalanceAccount(['balance' => 50.0]);
    assignAccountTarget($account);

    runLowBalanceCheck($account);
    expect($account->fresh()?->low_balance_notified_at)->not->toBeNull();

    $account->update(['balance' => 150.0]);
    runLowBalanceCheck($account);
    expect($account->fresh()?->low_balance_notified_at)->toBeNull();

    $account->update(['balance' => 50.0]);
    runLowBalanceCheck($account);

    Notification::assertSentToTimes(accountOwner($account), AccountLowBalanceNotification::class, 2);
});

it('does nothing for an unknown account', function (): void {
    Notification::fake();

    resolve(CheckAccountLowBalance::class)('missing-id');

    Notification::assertNothingSent();
});

it('does not notify when another save claimed the alert in the meantime', function (): void {
    Notification::fake();

    $account = lowBalanceAccount(['balance' => 50.0]);
    assignAccountTarget($account);

    claimLowBalanceAlertAfterLookup($account);

    runLowBalanceCheck($account);

    Notification::assertNothingSent();
});

it('does nothing for an inactive account', function (): void {
    Notification::fake();

    $account = lowBalanceAccount(['balance' => 50.0, 'is_active' => false]);
    assignAccountTarget($account);

    runLowBalanceCheck($account);

    Notification::assertNothingSent();
});

it('does nothing when notify_on_low_balance is disabled', function (): void {
    Notification::fake();

    $account = lowBalanceAccount(['balance' => 50.0, 'notify_on_low_balance' => false]);
    assignAccountTarget($account);

    runLowBalanceCheck($account);

    Notification::assertNothingSent();
});

it('marks as notified without sending when no targets are assigned', function (): void {
    Notification::fake();

    $account = lowBalanceAccount(['balance' => 50.0]);

    runLowBalanceCheck($account);

    Notification::assertNothingSent();
    expect($account->fresh()?->low_balance_notified_at)->not->toBeNull();
});

it('writes a database notification through the dynamic channel', function (): void {
    $account = lowBalanceAccount(['balance' => 50.0]);
    assignAccountTarget($account);

    runLowBalanceCheck($account);

    expect(DB::table('notifications')->count())->toBe(1);
});

it('rolls the alert back together with a failed booking', function (): void {
    $account = lowBalanceAccount();
    assignAccountTarget($account);
    $category = Category::factory()->create(['group' => CategoryGroup::VarExpenses]);

    expect(fn () => DB::transaction(function () use ($account, $category): void {
        resolve(TransactionService::class)->create([
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'amount' => 50.0,
            'date_time' => now(),
        ]);

        throw new RuntimeException('Booking failed');
    }))->toThrow(RuntimeException::class)
        ->and($account->fresh()?->low_balance_notified_at)->toBeNull()
        ->and(DB::table('notifications')->count())->toBe(0);
});
