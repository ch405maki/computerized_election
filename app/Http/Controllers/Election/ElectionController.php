<?php

namespace App\Http\Controllers\Election;

use App\Models\Election;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ElectionController extends Controller
{
    public function index()
    {
        $elections = Election::latest()->get();
        return Inertia::render('Elections/Index', [
            'elections' => $elections
        ]);
    }

    // --- Add this new method ---
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        // Check if the provided password matches the currently authenticated user's password
        if (Hash::check($request->password, $request->user()->password)) {
            return response()->json(['message' => 'Password verified successfully']);
        }

        return response()->json([
            'message' => 'The provided password does not match our records.'
        ], 422);
    }
    // ---------------------------

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,completed,upcoming',
        ]);

        $election = Election::create($validated);

        return response()->json(['message' => 'Election created successfully!', 'election' => $election], 201);
    }

    public function update(Request $request, Election $election)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|in:active,completed,upcoming',
        ]);

        if ($request->status === 'active' && $election->status !== 'active') {
            
            $activeExists = Election::where('status', 'active')->exists();

            if ($activeExists) {
                return response()->json([
                    'message' => 'Another election is currently active.'
                ], 400); 
            }
        }

        // 3. If the checks pass, process the update
        $election->update($request->all());

        return response()->json([
            'message' => 'Election updated successfully.',
            'election' => $election
        ]);
    }

    public function destroy(Election $election)
    {
        try {
            $election->delete();
            return response()->json(['message' => 'Election deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete election',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}