<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;

final class UserRelationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->guest()) {
            return;
        }

        $builder->whereIn('account_id', function (QueryBuilder $query): void {
            $query->select('id')
                ->from('accounts')
                ->distinct()
                ->where('user_id', auth()->id());
        });
    }
}
