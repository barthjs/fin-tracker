<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ApiAbility;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\Exceptions\MissingAbilityException;
use Symfony\Component\HttpFoundation\Response;

final class CheckAbilities
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $abilityKey): Response
    {
        $user = $request->user();

        $ability = ApiAbility::from($abilityKey);

        $requiredAbility = $request->isMethod('GET')
            ? $ability->read()
            : $ability->write();

        if ($user === null || ! $user->tokenCan($requiredAbility)) {
            throw new MissingAbilityException($requiredAbility);
        }

        return $next($request);
    }
}
