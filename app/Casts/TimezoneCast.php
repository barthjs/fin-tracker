<?php

declare(strict_types=1);

namespace App\Casts;

use DateTimeZone;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * @implements CastsAttributes<string, string>
 */
final class TimezoneCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return is_string($value) ? $value : 'UTC';
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if (! is_string($value) || ! in_array($value, DateTimeZone::listIdentifiers(), true)) {
            $displayValue = is_string($value) ? $value : gettype($value);
            throw new InvalidArgumentException("Invalid timezone identifier: {$displayValue}");
        }

        return $value;
    }
}
