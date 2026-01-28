<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

final class UserService
{
    public function delete(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->accounts()->withoutGlobalScopes()->get()->each->delete();
            $user->categories()->withoutGlobalScopes()->get()->each->delete();
            $user->portfolios()->withoutGlobalScopes()->get()->each->delete();
            $user->securities()->withoutGlobalScopes()->get()->each->delete();

            $user->delete();
        });
    }
}
