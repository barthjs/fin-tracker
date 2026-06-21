<?php

declare(strict_types=1);

namespace App\Enums;

use App\Enums\Concerns\HasSortedOptions;
use Filament\Support\Contracts\HasLabel;

/**
 * The eleven sectors of the Global Industry Classification Standard (GICS).
 */
enum SecuritySector: string implements HasLabel
{
    use HasSortedOptions;

    case Energy = 'energy';
    case Materials = 'materials';
    case Industrials = 'industrials';
    case ConsumerDiscretionary = 'consumer_discretionary';
    case ConsumerStaples = 'consumer_staples';
    case HealthCare = 'health_care';
    case Financials = 'financials';
    case InformationTechnology = 'information_technology';
    case CommunicationServices = 'communication_services';
    case Utilities = 'utilities';
    case RealEstate = 'real_estate';

    /**
     * Returns the translated name of the sector.
     */
    public function getLabel(): string
    {
        return __('security.sector.'.$this->value);
    }

    /**
     * Returns the fixed chart color of the sector.
     */
    public function getChartColor(): string
    {
        return match ($this) {
            self::Energy => '#f97316',
            self::Materials => '#a16207',
            self::Industrials => '#64748b',
            self::ConsumerDiscretionary => '#ec4899',
            self::ConsumerStaples => '#84cc16',
            self::HealthCare => '#14b8a6',
            self::Financials => '#3b82f6',
            self::InformationTechnology => '#6366f1',
            self::CommunicationServices => '#8b5cf6',
            self::Utilities => '#eab308',
            self::RealEstate => '#ef4444',
        };
    }
}
