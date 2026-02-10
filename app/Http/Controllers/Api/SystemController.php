<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class SystemController
{
    use ApiResponse;

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

    /**
     * Returns the request payload
     *
     * Only available in debug mode.
     */
    public function webhook(Request $request): JsonResponse
    {
        if (! config()->get('app.debug')) {
            return $this->forbiddenResponse();
        }

        $header = $request->header('X-Signature-256');

        if (! $header || ! Str::contains($header, '=')) {
            return $this->forbiddenResponse();
        }

        $algorithm = Str::before($header, '=');
        $userSignature = Str::after($header, '=');

        $knownSignature = hash_hmac(
            algo: $algorithm,
            data: $request->getContent(),
            key: config()->string('app.webhook_secret')
        );

        if (! hash_equals($knownSignature, $userSignature)) {
            return $this->forbiddenResponse();
        }

        Log::debug('Webhook received', $request->all());

        return response()->json($request->all());
    }
}
