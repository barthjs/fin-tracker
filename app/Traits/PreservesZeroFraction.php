<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Keeps float values with a zero fraction as floats in the JSON output of a resource,
 * so that clients always receive the same number type for a field.
 */
trait PreservesZeroFraction
{
    /**
     * Returns the JSON encoding options for the resource response.
     */
    public function jsonOptions(): int
    {
        return JSON_PRESERVE_ZERO_FRACTION;
    }
}
