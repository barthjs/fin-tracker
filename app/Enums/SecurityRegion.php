<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasSortedOptions;
use Filament\Support\Contracts\HasLabel;

enum SecurityRegion: string implements HasLabel
{
    use HasSortedOptions;

    case Global = 'global';
    case NorthAmerica = 'north_america';
    case LatinAmerica = 'latin_america';
    case Europe = 'europe';
    case AsiaPacific = 'asia_pacific';
    case MiddleEastAfrica = 'middle_east_africa';

    /**
     * Returns the translated name of the region.
     */
    public function getLabel(): string
    {
        return __('security.region.'.$this->value);
    }

    /**
     * Returns the fixed chart color of the region.
     */
    public function getChartColor(): string
    {
        return match ($this) {
            self::Global => '#6366f1',
            self::NorthAmerica => '#3b82f6',
            self::LatinAmerica => '#f59e0b',
            self::Europe => '#10b981',
            self::AsiaPacific => '#ef4444',
            self::MiddleEastAfrica => '#8b5cf6',
        };
    }
}
