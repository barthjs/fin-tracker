<?php

declare(strict_types=1);

use App\Enums\SecurityRegion;
use App\Models\Security;
use App\Services\SecurityService;

beforeEach(fn () => asUser());

function securityService(): SecurityService
{
    return resolve(SecurityService::class);
}

it('builds a chart allocation grouped by classification', function (): void {
    Security::factory()->count(2)->create(['region' => SecurityRegion::Europe, 'price' => 10.0, 'total_quantity' => 3.0]);
    Security::factory()->create(['region' => null, 'price' => 10.0, 'total_quantity' => 1.0]);

    $result = securityService()->getChartAllocation(Security::query(), 'region', SecurityRegion::class);

    expect($result['labels'])->toBe([SecurityRegion::Europe->getLabel(), __('security.unassigned')])
        ->and($result['datasets'][0]['data'])->toBe([60.0, 10.0])
        ->and($result['datasets'][0]['backgroundColor'][0])->toBe(SecurityRegion::Europe->getChartColor());
});
