<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PreventMixedAuthentication
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $otherGuard = $guard === 'web' ? 'voter' : 'web';
        
        if (Auth::guard($otherGuard)->check()) {
            if ($guard === 'web') {
                return redirect()->route('vote.voting')->with('error', 'Please logout as voter first.');
            } else {
                return redirect()->route('dashboard')->with('error', 'Please logout as administrator first.');
            }
        }

        return $next($request);
    }
}