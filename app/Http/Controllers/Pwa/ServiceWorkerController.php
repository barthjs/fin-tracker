<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pwa;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

final readonly class ServiceWorkerController
{
    /**
     * Returns the service worker of the build from the root of the app, so that its scope covers the whole app.
     */
    public function __invoke(): BinaryFileResponse
    {
        $path = public_path('build/sw.js');

        // Only a build writes the service worker, the dev server of Vite registers none
        abort_unless(is_file($path), 404);

        return response()->file($path, [
            'Content-Type' => 'text/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
