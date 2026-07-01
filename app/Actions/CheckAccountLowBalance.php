<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\NotificationEventType;
use App\Models\Account;
use App\Notifications\AccountLowBalanceNotification;
use Illuminate\Support\Facades\DB;

/**
 * Check the balance of an account within the saving transaction and queue its low-balance notifications.
 *
 * The alert is sent once when the balance falls below the threshold and is armed again once the balance recovers.
 * The claim of the alert and the queued notifications are part of the same database transaction as the booking,
 * so they are committed or rolled back together. The conditional update of the claim locks the account row,
 * so concurrent saves cannot send the alert twice.
 */
final readonly class CheckAccountLowBalance
{
    /**
     * Runs the check for the account, if it is active and notifying, against the balance of the current transaction.
     */
    public function __invoke(string $accountId): void
    {
        DB::transaction(function () use ($accountId): void {
            $account = Account::query()
                ->withoutGlobalScopes()
                ->whereKey($accountId)
                ->where('is_active', true)
                ->where('notify_on_low_balance', true)
                ->first();

            if ($account === null) {
                return;
            }

            $this->check($account);
        });
    }

    /**
     * Sends the alert when the balance is below the threshold, or arms it again when the balance recovered.
     */
    private function check(Account $account): void
    {
        if ($account->balance >= $account->low_balance_threshold) {
            Account::query()
                ->withoutGlobalScopes()
                ->whereKey($account->id)
                ->whereNotNull('low_balance_notified_at')
                ->update(['low_balance_notified_at' => null]);

            return;
        }

        if (! $this->claim($account)) {
            return;
        }

        $targets = $account->getNotificationTargetsForEvent(NotificationEventType::ACCOUNT_LOW_BALANCE);

        foreach ($targets as $target) {
            $account->user->notify(new AccountLowBalanceNotification($account, $target));
        }
    }

    /**
     * Stores the send time of the alert if it is not sent yet, and reports whether this call won.
     */
    private function claim(Account $account): bool
    {
        $claimed = Account::query()
            ->withoutGlobalScopes()
            ->whereKey($account->id)
            ->whereNull('low_balance_notified_at')
            ->update(['low_balance_notified_at' => now()]);

        return $claimed === 1;
    }
}
