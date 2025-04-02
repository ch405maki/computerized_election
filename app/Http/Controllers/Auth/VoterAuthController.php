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
            return redirect()->route('vote.voting');
        }

        return back()->withErrors(['student_number' => 'Invalid credentials']);
    }

    public function logout()
    {
        Auth::guard('voter')->logout();
        return Inertia::render('Welcome');
    }
}