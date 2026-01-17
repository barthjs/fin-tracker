<?php

declare(strict_types=1);

namespace App\View\Components\Auth;

use App\Services\Oidc\OidcService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class Oidc extends Component
{
    /** @var array<array{key: string, label: string}> */
    public array $availableProviders = [];

    public function __construct(
        OidcService $oidcService,
    ) {
        foreach ($oidcService->getEnabledProviders() as $provider => $config) {
            $this->availableProviders[] = [
                'key' => $provider,
                'label' => $config['label'],
            ];
        }
    }

    public function render(): View
    {
        return view('components.auth.oidc', [
            'availableProviders' => $this->availableProviders,
        ]);
    }
}
