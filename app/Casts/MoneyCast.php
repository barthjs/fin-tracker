<?php

declare(strict_types=1);

namespace App\Casts;

use App\Models\Trade;
use App\Models\Transaction;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        return round((float) $value / 100, precision: 2);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string|float
    {
        if ((! $model instanceof Transaction) && (! $model instanceof Trade)) {
            return $value;
        }

        return round((float) $value * 100);
    }
}
