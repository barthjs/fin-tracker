<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Currency;
use App\Models\Account;
use App\Models\User;

/**
 * Retrieve or create the default account for a user.
 *
 * Attempts to find an account named 'Demo' for the given (or authenticated)
 * user. If no such account exists, a new one is created with that name
 * and a randomly generated color.
 */
final class GetOrCreateDefaultAccount
{
    public function __invoke(?User $user = null): Account
    {
        $user ??= auth()->user();

        return Account::query()->where('user_id', $user->id)->where('name', 'Demo')->first() ??
            Account::query()->create([
                'name' => 'Demo',
                'currency' => Currency::getCurrency(),
                'color' => mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => $user->id,
            ]);
    }
}
