<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // Ensure user is authenticated
        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // Get permissions array, default to empty array if null
        $permissions = $user->permissions ?? [];

        // Check if the specific permission exists and is true
        if (!isset($permissions[$permission]) || $permissions[$permission] !== true) {
            // If it's an API request, return JSON
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            
            // If it's a standard web request, show a 403 Forbidden error page
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}