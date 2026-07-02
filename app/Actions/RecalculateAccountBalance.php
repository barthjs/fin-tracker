<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\TradeType;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Trade;
use App\Models\Transaction;

/**
 * Recalculate and persist the balance for an account.
 *
 * The balance is derived from all financial activities linked to the account:
 * - Revenues increase the balance.
 * - Expenses decrease the balance.
 * - Transfers decrease the balance if the account is the source
 *   and increase it if the account is the target.
 * - Trades decrease the balance when assets are bought,
 *   and increase it when assets are sold.
 */
final readonly class RecalculateAccountBalance
{
    public function __construct(
        private CheckAccountLowBalance $checkAccountLowBalance,
    ) {}

    public function __invoke(string $accountId): void
    {
        // Serializes concurrent recalculations of the account, so each sum includes the other bookings.
        // Unlike FOR UPDATE, this lock does not conflict with the key share lock that the foreign key check
        // of the just inserted booking holds on the account, which would deadlock two concurrent bookings.
        Account::query()->whereKey($accountId)->lock('for no key update')->first(['id']);

        $revenue = (float) Transaction::query()->where('account_id', $accountId)
            ->where('type', TransactionType::Revenue)
            ->sum('amount');

        $expense = (float) Transaction::query()->where('account_id', $accountId)
            ->where('type', TransactionType::Expense)
            ->sum('amount');

        $outgoingTransfers = (float) Transaction::query()->where('account_id', $accountId)
            ->where('type', TransactionType::Transfer)
            ->sum('amount');

        $incomingTransfers = (float) Transaction::query()->where('transfer_account_id', $accountId)
            ->where('type', TransactionType::Transfer)
            ->sum('amount');

        $buyTrades = (float) Trade::query()->where('account_id', $accountId)
            ->where('type', TradeType::Buy)
            ->sum('total_amount');

        $sellTrades = (float) Trade::query()->where('account_id', $accountId)
            ->where('type', TradeType::Sell)
            ->sum('total_amount');

        $balance = $revenue - $expense - $outgoingTransfers + $incomingTransfers - $buyTrades + $sellTrades;

        Account::query()->whereKey($accountId)->update(['balance' => $balance]);

        ($this->checkAccountLowBalance)($accountId);
    }
}
