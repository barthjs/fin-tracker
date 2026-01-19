<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\ApiError;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    public function unauthorizedResponse(): JsonResponse
    {
        return $this->errorResponse(ApiError::UNAUTHORIZED);
    }

    public function forbiddenResponse(): JsonResponse
    {
        return $this->errorResponse(ApiError::FORBIDDEN);
    }

    public function notFoundResponse(): JsonResponse
    {
        return $this->errorResponse(ApiError::NOT_FOUND);
    }

    public function methodNotAllowedResponse(): JsonResponse
    {
        return $this->errorResponse(ApiError::METHOD_NOT_ALLOWED);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public function validationFailedResponse(array $errors): JsonResponse
    {
        return $this->errorResponse(
            ApiError::VALIDATION_FAILED,
            ApiError::VALIDATION_FAILED->getMessage(),
            $errors
        );
    }

    public function serverErrorResponse(): JsonResponse
    {
        return $this->errorResponse(ApiError::INTERNAL_ERROR);
    }

    /**
     * @param  array<string, mixed>|null  $errors
     */
    public function errorResponse(
        ApiError $error,
        ?string $customMessage = null,
        ?array $errors = null
    ): JsonResponse {
        return response()->json([
            'message' => $customMessage ?? $error->getMessage(),
            'errors' => $errors,
        ], $error->getStatusCode());
    }
}
