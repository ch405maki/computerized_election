<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Check if the user is logged in AND their role is 'admin'
        if ($request->user()?->role !== 'admin') {
            // Throw a 403 Forbidden error if not admin.
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
