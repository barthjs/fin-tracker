<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Category;
use App\Models\User;

/**
 * Retrieve or create the default category for a user.
 *
 * Attempts to find a category named 'Demo' for the given (or authenticated)
 * user. If no such category exists, a new one is created with that name
 * and a randomly generated color.
 */
final class GetOrCreateDefaultCategory
{
    public function __invoke(?User $user = null): Category
    {
        $user ??= auth()->user();

        return Category::query()->where('user_id', $user->id)->where('name', 'Demo')->first() ??
            Category::query()->create([
                'name' => 'Demo',
                'color' => mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => $user->id,
            ]);
    }
}
