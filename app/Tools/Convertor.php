<?php

declare(strict_types=1);

namespace App\Tools;

class Convertor
{
    public static function formatNumber(string $number): float
    {
        // Sanitize the input by removing all characters except digits, commas, periods, and signs.
        $sanitized = preg_replace('/[^0-9,.+-]/', '', $number);

        $sign = 1;
        if (empty($sanitized) || $sanitized === '-' || $sanitized === '+') {
            // Handle cases where the input is empty or just a sign.
            $floatValue = 0.0;
        } else {
            if (str_starts_with($sanitized, '-')) {
                $sign = -1; // Set sign for negative numbers
                $sanitized = substr($sanitized, 1);
            } elseif (str_starts_with($sanitized, '+')) {
                $sanitized = substr($sanitized, 1);
            }

            // Handle different formats with both period and comma present.
            if (str_contains($sanitized, '.') && str_contains($sanitized, ',')) {
                if (strrpos($sanitized, '.') < strrpos($sanitized, ',')) {
                    // Assume period as thousands separator, replace comma with period for decimal
                    $sanitized = str_replace(['.', ','], ['', '.'], $sanitized);
                } else {
                    // Assume comma as thousands separator
                    $sanitized = str_replace(',', '', $sanitized);
                }
            } else {
                // Treat comma as a decimal separator if present
                $sanitized = str_replace(',', '.', $sanitized);
            }
            // Convert sanitized string to a float and apply the sign.
            $floatValue = (float) $sanitized * $sign;
        }

        return $floatValue;
    }
}
