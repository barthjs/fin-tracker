<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasSortedOptions;
use Filament\Support\Contracts\HasLabel;

enum SecurityType: string implements HasLabel
{
    use HasSortedOptions;

    case Bond = 'bond';
    case Derivative = 'derivative';
    case ETF = 'etf';
    case Fund = 'fund';
    case Stock = 'stock';

    public function getLabel(): string
    {
        return __('security.type.'.$this->value);
    }
}
