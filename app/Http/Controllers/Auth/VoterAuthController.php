<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class VoterAuthController extends Controller
{
    public function showLoginForm()
    {
        // Check if already logged in as admin
        if (Auth::guard('web')->check()) {
            return redirect()->route('dashboard')->with('error', 'Please logout as administrator first.');
        }
        
        return Inertia::render('Welcome');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'student_number' => 'required',
            'password' => 'required',
        ]);

        $throttleKey = strtolower($request->input('student_number')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'student_number' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        if (Auth::guard('web')->check()) {
            return back()->withErrors([
                'invalid_credentials' => 'You are already logged in as an administrator. Please logout first.'
            ]);
        }

        if (Auth::guard('voter')->attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            $voter = Auth::guard('voter')->user();

            // 1. Fetch the currently active election
            // (Adjust the 'status' or 'is_active' column name based on your database schema)
            $activeElection = Election::where('status', 'active')->first();

            // 2. Prevent login if there is no ongoing election
            if (!$activeElection) {
                Auth::guard('voter')->logout();
                return back()->withErrors([
                    'student_number' => 'There are no active elections at the moment.'
                ]);
            }

            // 3. Check if the voter has already voted in THIS specific election
            if ($voter->hasVotedInElection($activeElection->id)) {
                Auth::guard('voter')->logout();
                return back()->withErrors([
                    'student_number' => 'You have already voted.'
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('vote.voting');
        }
        
        RateLimiter::hit($throttleKey);

        return back()->withErrors([
            'invalid_credentials' => 'Please check if your Student Number or Password is correct or email at auslitcweb@gmail.com for verification.'
        ]);
    }

    public function logout(Request $request)
    {
        // Only logout voter guard
        Auth::guard('voter')->logout();
        
        // Clear voter session data
        $request->session()->forget('password_hash_voter');
        
        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect('/');
    }
}