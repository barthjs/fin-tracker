<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

final class SystemController
{
    /**
     * Minimal status route for guest users.
     */
    public function up(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Detailed health route for auth users.
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Get the current app version.
     */
    public function version(): JsonResponse
    {
        return response()->json([
            'version' => config()->string('app.version'),
        ]);
    }
}
