<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use App\Tools\EnumQueryOrder;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasOrderedCases
{
    /**
     * Orders a query by the declaration order of the cases, so table sorting and groups follow the order of the enum.
     * Null values are placed after all cases.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function orderQueryUsing(Builder $query, Expression|string $column, string $direction): Builder
    {
        $positions = [];
        foreach (self::cases() as $position => $case) {
            $positions[(string) $case->value] = $position;
        }

        return EnumQueryOrder::apply($query, $column, $positions, $direction);
    }
}
