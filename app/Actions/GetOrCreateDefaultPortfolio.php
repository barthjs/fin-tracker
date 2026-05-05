<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Currency;
use App\Models\Portfolio;
use App\Models\User;

/**
 * Retrieve or create the default portfolio for a user.
 *
 * Attempts to find a portfolio named 'Demo' for the given (or authenticated)
 * user. If no such portfolio exists, a new one is created with that name
 * and a randomly generated color.
 */
final class GetOrCreateDefaultPortfolio
{
    public function __invoke(?User $user = null): Portfolio
    {
        $user ??= auth()->user();

        return Portfolio::query()->where('user_id', $user->id)->where('name', 'Demo')->first() ??
            Portfolio::query()->create([
                'name' => 'Demo',
                'currency' => Currency::getCurrency(),
                'color' => mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => $user->id,
            ]);
    }
}
