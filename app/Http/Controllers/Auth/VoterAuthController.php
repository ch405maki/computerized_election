<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    // Check if already logged in as admin
    if (Auth::guard('web')->check()) {
        return back()->withErrors([
            'invalid_credentials' => 'You are already logged in as an administrator. Please logout first.'
        ]);
    }

    if (Auth::guard('voter')->attempt($credentials)) {
        $voter = Auth::guard('voter')->user();

        if ($voter->hasVoted()) {
            Auth::guard('voter')->logout();
            return back()->withErrors(['student_number' => 'You have already voted.']);
        }

        // Regenerate session for security
        $request->session()->regenerate();

        // Redirect voter to platforms page after login
        return redirect()->route('platforms.index');
    }
    
    return back()->withErrors([
        'invalid_credentials' => 'Please check if your Student Number or Password is correct 
        or email at auslitcweb@gmail.com for verification.'
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