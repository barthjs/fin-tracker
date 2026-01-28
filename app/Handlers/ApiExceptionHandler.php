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
        $exceptions->render(function (AuthenticationException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return $this->unauthorizedResponse();
            }

            return null;
        });

        $exceptions->render(function (AuthorizationException|AccessDeniedHttpException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return $this->forbiddenResponse();
            }

            return null;
        });

        $exceptions->render(function (MissingAbilityException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return $this->errorResponse(
                    ApiError::FORBIDDEN,
                    'Missing required API token ability'
                );
            }

            return null;
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return $this->notFoundResponse();
            }

            return null;
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return $this->methodNotAllowedResponse();
            }

            return null;
        });

        $exceptions->render(function (ValidationException $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                /** @phpstan-ignore-next-line */
                return $this->validationFailedResponse($e->errors());
            }

            return null;
        });

        $exceptions->render(function (Throwable $e, Request $request): ?JsonResponse {
            if ($request->is('api/*')) {
                return $this->serverErrorResponse();
            }

            return null;
        });

        $exceptions->shouldRenderJsonWhen(fn (Request $request): bool => $request->is('api/*'));
    }
}
