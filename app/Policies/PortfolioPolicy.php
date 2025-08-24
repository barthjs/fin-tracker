<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Portfolio;
use App\Models\User;

final class PortfolioPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id
            && $portfolio->trades()->count() === 0;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Portfolio $portfolio): bool
    {
        return $user->id === $portfolio->user_id;
    }
}
