<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TradeType: string implements HasColor, HasLabel
{
    case Buy = 'buy';
    case Sell = 'sell';

    public function getLabel(): string
    {
        return __('trade.type.'.$this->value);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Buy => 'success',
            self::Sell => 'danger',
        };
    }
}
