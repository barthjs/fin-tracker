<?php

declare(strict_types=1);

namespace App\Filament\Resources\Portfolios\Pages;

use App\Filament\Concerns\HasResourceActions;
use App\Filament\Resources\Portfolios\PortfolioResource;
use Filament\Resources\Pages\ViewRecord;

final class ViewPortfolio extends ViewRecord
{
    use HasResourceActions;

    protected static string $resource = PortfolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            self::editAction(),
            self::deleteAction(),
        ];
    }
}
