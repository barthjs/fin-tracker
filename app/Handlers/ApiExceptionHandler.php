<?php

declare(strict_types=1);

namespace App\Handlers;

use App\Enums\ApiError;
use App\Traits\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiExceptionHandler
{
    use ApiResponse;

    public function register(Exceptions $exceptions): void
    {
        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if (! $request->is('api/*')) {
                return null;
            }

            return match (true) {
                $e instanceof AuthorizationException,
                $e instanceof AuthenticationException => $this->unauthorizedResponse(),

                $e instanceof AccessDeniedHttpException => $this->handleAuthorization($e),

                $e instanceof NotFoundHttpException => $this->notFoundResponse(),

                $e instanceof MethodNotAllowedHttpException => $this->methodNotAllowedResponse(),

                $e instanceof ValidationException => $this->handleValidation($e),

                // @codeCoverageIgnoreStart
                default => $this->serverErrorResponse($e, $request),
                // @codeCoverageIgnoreEnd
            };
        });

        $exceptions->shouldRenderJsonWhen(fn (Request $request): bool => $request->is('api/*'));
    }

    private function handleAuthorization(Throwable $e): JsonResponse
    {
        if ($e instanceof MissingAbilityException || $e->getPrevious() instanceof MissingAbilityException) {
            return $this->errorResponse(
                ApiError::FORBIDDEN,
                'Missing required API token ability'
            );
        }

        // @codeCoverageIgnoreStart
        return $this->forbiddenResponse();
        // @codeCoverageIgnoreEnd
    }

    private function handleValidation(ValidationException $e): JsonResponse
    {
        /** @phpstan-ignore-next-line */
        return $this->validationFailedResponse($e->errors());
    }
}
