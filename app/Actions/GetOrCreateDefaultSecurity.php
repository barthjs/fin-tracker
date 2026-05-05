<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Security;
use App\Models\User;

/**
 * Retrieve or create the default security for a user.
 *
 * Attempts to find a security named 'Demo' for the given (or authenticated)
 * user. If no such security exists, a new one is created with that name
 * and a randomly generated color.
 */
final class GetOrCreateDefaultSecurity
{
    public function __invoke(?User $user = null): Security
    {
        $user ??= auth()->user();

        return Security::query()->where('user_id', $user->id)->where('name', 'Demo')->first() ??
            Security::query()->create([
                'name' => 'Demo',
                'color' => mb_strtolower(sprintf('#%06X', random_int(0, 0xFFFFFF))),
                'user_id' => $user->id,
            ]);
    }
}
