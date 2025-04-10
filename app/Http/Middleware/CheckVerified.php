<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVerified
{
    /**
     * Check if the user is verified
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $profileUri = __('user.profile-slug');
        $currentUri = $request->route()->uri();

        if (! in_array($currentUri, [$profileUri, 'login', 'register']) && ! auth()->user()->verified) {
            return redirect($profileUri);
        }

        return $next($request);
    }
}
