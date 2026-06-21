<?php

declare(strict_types=1);

namespace App\Tools;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class EnumQueryOrder
{
    /**
     * Orders a query by a fixed position per enum value of a column and places null values after all positions.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<string, int>  $positions
     * @return Builder<TModel>
     */
    public static function apply(Builder $query, Expression|string $column, array $positions, string $direction): Builder
    {
        $wrappedColumn = $query->getQuery()->getGrammar()->wrap($column);

        $cases = [];
        foreach ($positions as $value => $position) {
            $cases[] = sprintf("WHEN '%s' THEN %d", $value, $position);
        }

        $caseSql = sprintf(
            'CASE WHEN %1$s IS NULL THEN %2$d ELSE (CASE %1$s %3$s END) END',
            $wrappedColumn,
            count($positions),
            implode(' ', $cases),
        );

        /** @phpstan-ignore argument.type (SQL built from enum cases) */
        return $query->orderByRaw($caseSql.' '.($direction === 'desc' ? 'desc' : 'asc'));
    }
}
