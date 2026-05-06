<?php

declare(strict_types=1);

use App\Tools\Convertor;

it('parses numbers in various formats', function (string $input, float $expected): void {
    expect(Convertor::formatNumber($input))->toBe($expected);
})->with([
    'plain integer' => ['1234', 1234.0],
    'european format' => ['1.234,56', 1234.56],
    'us format' => ['1,234.56', 1234.56],
    'comma decimal' => ['12,5', 12.5],
    'negative comma decimal' => ['-5,5', -5.5],
    'leading plus' => ['+10', 10.0],
    'currency symbols stripped' => ['€ 1.000,00', 1000.0],
    'empty string' => ['', 0.0],
    'only minus' => ['-', 0.0],
    'only plus' => ['+', 0.0],
]);
