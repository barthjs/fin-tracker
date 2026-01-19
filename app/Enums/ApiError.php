<?php

declare(strict_types=1);

namespace App\Enums;

enum ApiError: string
{
    case UNAUTHORIZED = 'unauthorized';
    case FORBIDDEN = 'forbidden';
    case NOT_FOUND = 'not_found';
    case METHOD_NOT_ALLOWED = 'method_not_allowed';
    case VALIDATION_FAILED = 'validation_failed';
    case INTERNAL_ERROR = 'internal_error';

    public function getMessage(): string
    {
        return match ($this) {
            self::UNAUTHORIZED => 'Unauthorized',
            self::FORBIDDEN => 'Forbidden',
            self::NOT_FOUND => 'Not Found',
            self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
            self::VALIDATION_FAILED => 'Unprocessable Content',
            self::INTERNAL_ERROR => 'Internal Server Error',
        };
    }

    public function getStatusCode(): int
    {
        return match ($this) {
            self::UNAUTHORIZED => 401,
            self::FORBIDDEN => 403,
            self::NOT_FOUND => 404,
            self::METHOD_NOT_ALLOWED => 405,
            self::VALIDATION_FAILED => 422,
            self::INTERNAL_ERROR => 500,
        };
    }
}
