<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Account;

final class AccountObserver
{
    public function creating(Account $account): void
    {
        $account->name = mb_trim($account->name);

        /** @phpstan-ignore-next-line */
        if ($account->user_id === null) {
            $account->user_id = auth()->user()->id;
        }
    }

    public function updating(Account $account): void
    {
        $account->name = mb_trim($account->name);
    }
}
