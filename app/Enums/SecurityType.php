<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SecurityType: string implements HasLabel
{
    public function getLabel(): ?string
    {
        return $this->name;
    }

    case BOND = 'BOND';
    case DERIVATIVE = 'DERIVATIVE';
    case ETF = 'ETF';
    case FUND = 'FUND';
    case STOCK = 'STOCK';
}
