<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

final class UserRelationScope implements Scope
{
    /**
     * Apply scope: filter Trades and Transactions by the authenticated user's related entities.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (App::runningInConsole()) {
            return;
        }

        $table = $model->getTable();
        $userId = auth()->id();

        // Trades: filter via account
        if ($table === 'trades' && $model->relationLoaded('account') || method_exists($model, 'account')) {
            $builder->whereHas('account', fn (Builder $query): Builder => $query->where('user_id', $userId));
        }

        // Transactions: filter via account or category
        if ($table === 'transactions') {
            if (method_exists($model, 'account')) {
                $builder->whereHas('account', fn (Builder $query): Builder => $query->where('user_id', $userId));
            }

            if (method_exists($model, 'category')) {
                $builder->orWhereHas('category', fn (Builder $query): Builder => $query->where('user_id', $userId));
            }
        }
    }
}
