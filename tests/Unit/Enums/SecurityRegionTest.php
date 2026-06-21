<?php

declare(strict_types=1);

use App\Enums\SecurityRegion;

it('returns a distinct chart color for every region', function (): void {
    $colors = array_map(fn (SecurityRegion $region): string => $region->getChartColor(), SecurityRegion::cases());

    expect(array_unique($colors))->toHaveCount(count(SecurityRegion::cases()));
});
