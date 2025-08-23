<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SecurityType: string implements HasLabel
{
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
