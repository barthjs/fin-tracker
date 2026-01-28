<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Filament\Pages\Auth\EditProfile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsVerified
{
    /**
     * Redirects to the profile page if the user is not verified.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentUri = $request->route()?->uri();

        $allowedRoutes = [
            'profile',
            'login',
            'register',
            'logout',
        ];

        if (! in_array($currentUri, $allowedRoutes, true) && ! auth()->user()->is_verified) {
            return redirect(EditProfile::getUrl());
        }

        return $next($request);
    }
}
