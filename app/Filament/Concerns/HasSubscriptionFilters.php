<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property array<string, array<string, mixed>> $tableFilters
 */
trait HasSubscriptionFilters
{
    /**
     * @param  Builder<Subscription>  $query
     * @return Builder<Subscription>
     */
    protected function applyFilters(Builder $query): Builder
    {
        $filters = $this->tableFilters;

        return $query
            ->when(
                $filters['account_id']['values'] ?? null,
                fn (Builder $q, mixed $values): Builder => $q->whereIn('account_id', (array) $values)
            )
            ->when(
                $filters['category_id']['values'] ?? null,
                fn (Builder $q, mixed $values): Builder => $q->whereIn('category_id', (array) $values)
            )
            ->when(
                $filters['period_unit']['value'] ?? null,
                fn (Builder $q, mixed $value): Builder => $q->where('period_unit', $value)
            )
            ->when(
                isset($filters['auto_generate_transaction']['value']),
                fn (Builder $q): Builder => $q->where('auto_generate_transaction', $filters['auto_generate_transaction']['value'])
            )
            ->when(
                $filters['inactive']['isActive'] ?? false,
                fn (Builder $q): Builder => $q->where('is_active', false),
                fn (Builder $q): Builder => $q->where('is_active', true)
            );
    }
}
