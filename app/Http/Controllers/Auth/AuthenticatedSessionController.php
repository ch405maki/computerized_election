<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Inertia\{Inertia, Response};

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        // Check if already logged in as voter
        if (Auth::guard('voter')->check()) {
            return redirect()->route('vote.voting')->with('error', 'Please logout as voter first.');
        }

        return Inertia::render('auth/Login', [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Check if already logged in as voter
        if (Auth::guard('voter')->check()) {
            return back()->withErrors([
                'username' => 'You are already logged in as a voter. Please logout first.'
            ]);
        }

        $request->authenticate();
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        // Only logout web guard
        Auth::guard('web')->logout();
        
        // Clear admin session data
        $request->session()->forget('password_hash_web');
        
        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}