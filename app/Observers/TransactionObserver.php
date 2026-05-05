<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\GetOrCreateDefaultAccount;
use App\Actions\GetOrCreateDefaultCategory;
use App\Enums\TransactionType;
use App\Models\Transaction;

final readonly class TransactionObserver
{
    public function __construct(
        private GetOrCreateDefaultAccount $getOrCreateDefaultAccount,
        private GetOrCreateDefaultCategory $getOrCreateDefaultCategory,
    ) {}

    public function creating(Transaction $transaction): void
    {
        $transaction->payee = $transaction->payee === null ? null : mb_trim($transaction->payee);

        if ($transaction->type !== TransactionType::Transfer) {
            $transaction->transfer_account_id = null;
        }

        // Only needed in importer
        /** @phpstan-ignore-next-line */
        if ($transaction->account_id === null) {
            $transaction->account_id = ($this->getOrCreateDefaultAccount)()->id;
        }

        // Only needed in importer
        /** @phpstan-ignore-next-line */
        if ($transaction->category_id === null) {
            $transaction->category_id = ($this->getOrCreateDefaultCategory)()->id;
        }
    }

    public function updating(Transaction $transaction): void
    {
        $transaction->payee = $transaction->payee === null ? null : mb_trim($transaction->payee);

        if ($transaction->type !== TransactionType::Transfer) {
            $transaction->transfer_account_id = null;
        }
    }
}
