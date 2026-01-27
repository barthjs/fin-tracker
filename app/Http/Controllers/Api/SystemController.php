<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

final class SystemController
{
    /**
     * Check system availability
     */
    public function up(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
        ]);
    }

    /**
     * Get system health status
     */
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'environment' => app()->environment(),
        ]);
    }

    /**
     * Get the application version
     */
    public function version(): JsonResponse
    {
        return response()->json([
            'version' => config()->string('app.version'),
        ]);
    }
}
