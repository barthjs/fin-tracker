<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\CheckAccountLowBalance;
use App\Enums\NotificationEventType;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

final readonly class AccountService
{
    public function __construct(
        private CheckAccountLowBalance $checkAccountLowBalance,
    ) {}

    /**
     * Create an account, sync its notification assignments and run a low-balance check.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Account
    {
        return DB::transaction(function () use ($data): Account {
            $account = new Account();

            $hasTargets = array_key_exists('low_balance_targets', $data);
            $lowBalanceTargets = $account->extractNotificationTargets($data, 'low_balance_targets');

            $account->fill($data);
            $account->save();

            if ($hasTargets) {
                $account->syncNotificationAssignments(NotificationEventType::ACCOUNT_LOW_BALANCE, $lowBalanceTargets);
            }

            ($this->checkAccountLowBalance)($account->id);

            return $account;
        });
    }

    /**
     * Update an account, sync its notification assignments and run a low-balance check.
     *
     * Notification targets are only synced when the data contains them, so partial updates keep the existing ones.
     * Changing the threshold or switching the notification or the account on again allows the alert to be sent again.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Account $account, array $data): Account
    {
        return DB::transaction(function () use ($account, $data): Account {
            $hasTargets = array_key_exists('low_balance_targets', $data);
            $lowBalanceTargets = $account->extractNotificationTargets($data, 'low_balance_targets');

            $account->update($data);

            // A changed threshold or a notification switched on again reports a balance that is still below it
            if ($account->wasChanged(['low_balance_threshold', 'notify_on_low_balance', 'is_active'])) {
                $account->updateQuietly(['low_balance_notified_at' => null]);
            }

            if ($hasTargets) {
                $account->syncNotificationAssignments(NotificationEventType::ACCOUNT_LOW_BALANCE, $lowBalanceTargets);
            }

            ($this->checkAccountLowBalance)($account->id);

            return $account;
        });
    }
}
