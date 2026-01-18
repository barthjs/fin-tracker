<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\Oidc\OidcService;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    $this->service = app(OidcService::class);
});

test('it returns only enabled providers', function () {
    Config::set('services.authelia.oidc_enabled', true);
    Config::set('services.authelia.label', 'Authelia');
    Config::set('services.authentik.oidc_enabled', false);

    $enabled = $this->service->getEnabledProviders();

    expect($enabled)->toHaveKey('authelia')
        ->and($enabled)->not->toHaveKey('authentik')
        ->and($enabled['authelia']['label'])->toBe('Authelia');
});
