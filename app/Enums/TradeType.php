<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum TradeType: string implements HasLabel
{
    public function getLabel(): ?string
    {
        return $this->name;
    }

    case BUY = 'BUY';
    case SELL = 'SELL';
}
