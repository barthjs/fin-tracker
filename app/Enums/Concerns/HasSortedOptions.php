<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use App\Tools\EnumQueryOrder;
use Collator;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-require-implements HasLabel
 */
trait HasSortedOptions
{
    /**
     * Returns the options for select fields and filters, sorted by the translated label of the current locale.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        $collator = new Collator(app()->getLocale());
        $collator->asort($options);

        return $options;
    }

    /**
     * Resolves a case from its value or its translated label, ignoring letter case.
     */
    public static function tryFromValueOrLabel(string $search): ?self
    {
        $search = mb_strtolower(mb_trim($search));

        foreach (self::cases() as $case) {
            if (mb_strtolower((string) $case->value) === $search || mb_strtolower($case->getLabel()) === $search) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Orders a query by the label order of `options()`, so table groups follow the dropdown order.
     * Null values are placed after all cases.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function orderQueryUsing(Builder $query, Expression|string $column, string $direction): Builder
    {
        $positions = array_flip(array_keys(self::options()));

        return EnumQueryOrder::apply($query, $column, $positions, $direction);
    }
}
