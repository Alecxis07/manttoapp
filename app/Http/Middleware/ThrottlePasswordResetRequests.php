<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePasswordResetRequests
{
    /**
     * Apply RNF-SEG-004 rate limiting to Fortify password recovery.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') || ! $request->is('forgot-password')) {
            return $next($request);
        }

        return app(ThrottleRequests::class)->handle($request, $next, 'password-reset');
    }
}
