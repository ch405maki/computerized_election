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
        // Eager load the votingThreshold relationship
        $elections = Election::with('votingThreshold')->latest()->get();
        
        return Inertia::render('Elections/Index', [
            'elections' => $elections
        ]);
    }

    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (Hash::check($request->password, $request->user()->password)) {
            return response()->json(['message' => 'Password verified successfully']);
        }

        return response()->json([
            'message' => 'The provided password does not match our records.'
        ], 422);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'required_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Merge the request data with the forced 'upcoming' status
        $electionData = array_merge(
            $request->only(['name', 'start_date', 'end_date']),
            ['status' => 'upcoming']
        );

        // Create the election
        $election = Election::create($electionData);

        // Check if a required_percentage was provided and create the relationship
        if ($request->filled('required_percentage')) {
            $election->votingThreshold()->create([
                'required_percentage' => $request->required_percentage,
            ]);
        }

        // Load the relationship so the frontend receives the complete object
        $election->load('votingThreshold');

        return response()->json([
            'message' => 'Election created successfully!', 
            'election' => $election
        ], 201);
    }

    public function update(Request $request, Election $election)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|in:active,completed,upcoming', // Kept for editing purposes
            
            // Validation for threshold fields
            'required_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->status === 'active' && $election->status !== 'active') {
            $activeExists = Election::where('status', 'active')->exists();

            if ($activeExists) {
                return response()->json([
                    'message' => 'Another election is currently active.'
                ], 400); 
            }
        }

        // Process main election update
        $election->update($request->only(['name', 'start_date', 'end_date', 'status']));

        // Process voting threshold update using updateOrCreate
        if ($request->has('required_percentage')) {
            $election->votingThreshold()->updateOrCreate(
                ['election_id' => $election->id],
                [
                    'required_percentage' => $request->required_percentage,
                ]
            );
        }

        // Return data matching your Vue component's expectations
        return response()->json([
            'message' => 'Election updated successfully.',
            'data' => $election->load('votingThreshold'),
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