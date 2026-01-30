<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PeriodUnit: string implements HasColor, HasLabel
{
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';

    public function getLabel(): string
    {
        return trans_choice("subscription.units.$this->value", 1);
    }

    public function getLabelByFrequency(int $count): string
    {
        if ($count === 1) {
            return __("subscription.interval.single.$this->value");
        }

        return __('subscription.interval.multiple', [
            'count' => $count,
            'unit' => trans_choice("subscription.units.$this->value", $count),
        ]);
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Day => 'gray',
            self::Week => 'info',
            self::Month => 'warning',
            self::Year => 'success',
        };
    }
}
