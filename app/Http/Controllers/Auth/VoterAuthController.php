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
        return Inertia::render('Welcome');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'student_number' => 'required',
            'password' => 'required',
        ]);

        if (Auth::guard('voter')->attempt($credentials)) {
            $voter = Auth::guard('voter')->user();

            if ($voter->hasVoted()) {
                Auth::guard('voter')->logout();
                $request->session()->invalidate();      
                $request->session()->regenerateToken(); 
    
                return back()->withErrors(['student_number' => 'You have already voted.']);
            }

            return redirect()->route('vote.voting');
        }
        return back()->withErrors([
            'invalid_credentials' => 'Please check if your Student Number or Password is correct 
            or email at auslitcweb@gmail.com for verification.'
            ]);
    }


    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Inertia::render('Welcome');
    }
}