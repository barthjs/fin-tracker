<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Security;
use App\Models\User;

final class SecurityPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Security $security): bool
    {
        return $user->id === $security->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Security $security): bool
    {
        return $user->id === $security->user_id || $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Security $security): bool
    {
        return $user->id === $security->user_id
            && $security->trades()->count() === 0;
    }
}
