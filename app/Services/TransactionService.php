<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TransactionService
{
    /**
     * Create a transaction and recalculate balances and statistics.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Transaction
    {
        return DB::transaction(function () use ($data): Transaction {
            /** @var Transaction $transaction */
            $transaction = Transaction::create($data);

            $accountIds = array_filter([
                $transaction->account_id,
                $transaction->transfer_account_id,
            ]);

            $this->updateAccountBalances($accountIds);
            $this->updateStats($transaction->category_id, $transaction->date_time);

            return $transaction;
        });
    }

    /**
     * Update a transaction and recalculate balances and statistics.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Transaction $transaction, array $data): Transaction
    {
        return DB::transaction(function () use ($transaction, $data): Transaction {
            /** @var CarbonInterface $oldDate */
            $oldDate = $transaction->getOriginal('date_time');
            /** @var TransactionType $oldType */
            $oldType = $transaction->getOriginal('type');
            /** @var float $oldAmount */
            $oldAmount = $transaction->getOriginal('amount');
            /** @var string $oldAccountId */
            $oldAccountId = $transaction->getOriginal('account_id');
            /** @var string $oldCategoryId */
            $oldCategoryId = $transaction->getOriginal('category_id');
            /** @var string|null $oldTransferAccountId */
            $oldTransferAccountId = $transaction->getOriginal('transfer_account_id');

            $transaction->update($data);

            $balanceRelevantChanged = $oldAmount !== $transaction->amount
                || $oldType !== $transaction->type
                || $oldAccountId !== $transaction->account_id
                || $oldTransferAccountId !== $transaction->transfer_account_id;

            if ($balanceRelevantChanged) {
                $accountIds = array_filter([
                    $oldAccountId,
                    $transaction->account_id,
                    $oldTransferAccountId,
                    $transaction->transfer_account_id,
                ]);
                $this->updateAccountBalances(array_unique($accountIds));
            }

            $statsRelevantChanged = $balanceRelevantChanged
                || $transaction->date_time->notEqualTo($oldDate)
                || $oldCategoryId !== $transaction->category_id;

            if ($statsRelevantChanged) {
                $this->updateStats($oldCategoryId, $oldDate);
                $this->updateStats($transaction->category_id, $transaction->date_time);
            }

            return $transaction;
        });
    }

    /**
     * Delete a transaction and recalculate balances and statistics.
     */
    public function delete(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $date = $transaction->date_time;
            $categoryId = $transaction->category_id;

            $accountIds = array_filter([
                $transaction->account_id,
                $transaction->transfer_account_id,
            ]);

            $transaction->delete();

            $this->updateAccountBalances($accountIds);
            $this->updateStats($categoryId, $date);
        });
    }

    /**
     * Edit the account of multiple transactions.
     *
     * @param  Collection<int, Transaction>  $transactions
     * @param  array{account_id: string}  $data
     */
    public function bulkEditAccount(Collection $transactions, array $data): void
    {
        if ($transactions->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($transactions, $data) {
            /** @var array<string> $oldAccountIds */
            $oldAccountIds = $transactions->pluck('account_id')->unique()->toArray();

            Transaction::query()
                ->whereIn('id', $transactions->pluck('id')->toArray())
                ->update(['account_id' => $data['account_id']]);

            $allAccountIds = array_unique(array_merge($oldAccountIds, [$data['account_id']]));
            $this->updateAccountBalances($allAccountIds);
        });
    }

    /**
     * Delete multiple transactions and recalculate balances and statistics.
     *
     * @param  Collection<int, Transaction>  $transactions
     */
    public function bulkDelete(Collection $transactions): void
    {
        if ($transactions->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($transactions): void {
            $accountIds = [];
            /** @var array<array{id: string, date: CarbonInterface}> $categoryDates */
            $categoryDates = [];

            foreach ($transactions as $transaction) {
                $accountIds[] = $transaction->account_id;
                $accountIds[] = $transaction->transfer_account_id;

                $categoryDates[] = [
                    'id' => $transaction->category_id,
                    'date' => $transaction->date_time,
                ];
            }

            Transaction::query()
                ->whereIn('id', $transactions->pluck('id')->toArray())
                ->delete();

            $this->updateAccountBalances(array_unique(array_filter($accountIds)));

            foreach ($categoryDates as $cat) {
                $this->updateStats($cat['id'], $cat['date']);
            }
        });
    }

    /**
     * @param  array<string>  $accountIds
     */
    private function updateAccountBalances(array $accountIds): void
    {
        foreach ($accountIds as $id) {
            Account::updateAccountBalance($id);
        }
    }

    private function updateStats(string $categoryId, CarbonInterface $date): void
    {
        Transaction::updateCategoryStatistics($categoryId, $date);
    }
}
