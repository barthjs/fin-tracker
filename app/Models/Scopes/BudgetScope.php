<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * @implements Scope<Model>
 */
final class BudgetScope implements Scope
{
    /**
     * Query only for budgets with a category belonging to the authenticated user.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $builder->whereHas('category', function (Builder $query): void {
                $query->where('user_id', auth()->id());
            });
        }
    }
}
