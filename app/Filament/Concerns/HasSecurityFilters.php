<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Models\Security;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property array<string, array<string, mixed>> $tableFilters
 */
trait HasSecurityFilters
{
    /**
     * Applies the filters of the security table to the given query.
     *
     * @param  Builder<Security>  $query
     * @return Builder<Security>
     */
    protected function applyFilters(Builder $query): Builder
    {
        $filters = $this->tableFilters;

        return $query
            ->when(
                $filters['region']['values'] ?? null,
                fn (Builder $q, mixed $values): Builder => $q->whereIn('region', (array) $values)
            )
            ->when(
                $filters['sector']['values'] ?? null,
                fn (Builder $q, mixed $values): Builder => $q->whereIn('sector', (array) $values)
            )
            ->when(
                $filters['country']['values'] ?? null,
                fn (Builder $q, mixed $values): Builder => $q->whereIn('country', (array) $values)
            )
            ->when(
                $filters['type']['values'] ?? null,
                fn (Builder $q, mixed $values): Builder => $q->whereIn('type', (array) $values)
            )
            ->when(
                $filters['inactive']['isActive'] ?? false,
                fn (Builder $q): Builder => $q->where('is_active', false),
                fn (Builder $q): Builder => $q->where('is_active', true)
            );
    }
}
