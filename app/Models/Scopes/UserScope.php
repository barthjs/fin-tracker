<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class UserScope implements Scope
{
    /**
     * Query only for records belonging to the authenticated user
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check()) {
            $builder->where('user_id', auth()->id());
        }
    }
}
