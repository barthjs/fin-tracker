<?php

declare(strict_types=1);

namespace App\Filament\Resources\Securities\Widgets;

use App\Enums\SecuritySector;
use App\Filament\Concerns\HasSecurityFilters;
use App\Filament\Resources\Securities\Pages\ListSecurities;
use App\Models\Security;
use App\Services\SecurityService;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageTable;

final class SecuritySectorChart extends ChartWidget
{
    use HasSecurityFilters;
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    /**
     * Returns the translated heading of the chart.
     */
    public function getHeading(): string
    {
        return __('security.fields.sector');
    }

    /**
     * Returns the market value of the filtered securities per sector.
     */
    protected function getData(): array
    {
        $query = Security::query();
        $this->applyFilters($query);

        return resolve(SecurityService::class)->getChartAllocation($query, 'sector', SecuritySector::class);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function getTablePage(): string
    {
        return ListSecurities::class;
    }

    /**
     * Returns the chart type.
     */
    protected function getType(): string
    {
        return 'pie';
    }
}
