<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(401, 'Unauthenticated.');
        }

        // If the user is a superadmin or admin, let them through immediately!
        $role = strtolower($user->role);
        if ($role === 'superadmin' || $role === 'admin') {
            return $next($request);
        }

        // Get permissions array, default to empty array if null
        $permissions = $user->permissions ?? [];

        // Check if the specific permission exists and is true
        if (!isset($permissions[$permission]) || $permissions[$permission] !== true) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}