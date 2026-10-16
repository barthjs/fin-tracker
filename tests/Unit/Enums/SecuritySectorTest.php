<?php

declare(strict_types=1);

use App\Enums\SecuritySector;

it('returns a distinct chart color for every sector', function (): void {
    $colors = array_map(fn (SecuritySector $sector): string => $sector->getChartColor(), SecuritySector::cases());

    expect(array_unique($colors))->toHaveSameSize(SecuritySector::cases());
});
