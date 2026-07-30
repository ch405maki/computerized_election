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

        $role = strtolower($user->role);
        if ($role === 'superadmin') {
            return $next($request);
        }

        $permissions = $user->permissions ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true);
        }

        $hasPermission = isset($permissions[$permission]) && 
                        ($permissions[$permission] === true || 
                        $permissions[$permission] === 'true' || 
                        $permissions[$permission] === 1);

        if (!$hasPermission) {
            if ($request->wantsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }
            
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}