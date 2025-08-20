<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CheckVerified
{
    /**
     * Check if the user is verified.
     *
     * Redirects to the profile page if not verified.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $profileUri = __('user.profile-slug');
        $currentUri = $request->route()?->uri();

        $allowedRoutes = [
            $profileUri,
            'login',
            'register',
            'logout',
        ];

        if (! in_array($currentUri, $allowedRoutes, true) && ! auth()->user()->is_verified) {
            return redirect($profileUri);
        }

        return $next($request);
    }
}
