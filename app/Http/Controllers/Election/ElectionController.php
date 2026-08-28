<?php

namespace App\Http\Controllers\Election;

use App\Models\Election;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
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
            'voting_start_time' => 'nullable|date_format:H:i',
            'voting_end_time' => 'nullable|date_format:H:i|after:voting_start_time',
            'required_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        // Setup base election data
        $electionData = array_merge(
            $request->only(['name', 'start_date', 'end_date']),
            ['status' => 'upcoming']
        );

        // Convert strict HTML time (H:i) to a valid DateTime by prepending the start_date
        if ($request->filled('voting_start_time')) {
            $baseDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $electionData['voting_start_time'] = $baseDate . ' ' . $request->voting_start_time . ':00';
        }

        if ($request->filled('voting_end_time')) {
            $baseDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $electionData['voting_end_time'] = $baseDate . ' ' . $request->voting_end_time . ':00';
        }

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
            'voting_start_time' => 'nullable|date_format:H:i',
            'voting_end_time' => 'nullable|date_format:H:i|after:voting_start_time',
            'status' => 'sometimes|in:active,completed,upcoming,close', // Included 'close'
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

        // Gather standard fields
        $updateData = $request->only(['name', 'start_date', 'end_date', 'status']);

        // Handle daily voting times update
        if ($request->has('voting_start_time') || $request->has('voting_end_time')) {
            $startDateStr = $request->input('start_date', $election->start_date);
            $baseDate = Carbon::parse($startDateStr)->format('Y-m-d');

            if ($request->filled('voting_start_time')) {
                $updateData['voting_start_time'] = $baseDate . ' ' . $request->voting_start_time . ':00';
            } else {
                $updateData['voting_start_time'] = null;
            }

            if ($request->filled('voting_end_time')) {
                $updateData['voting_end_time'] = $baseDate . ' ' . $request->voting_end_time . ':00';
            } else {
                $updateData['voting_end_time'] = null;
            }
        }

        $election->update($updateData);

        if ($request->has('required_percentage')) {
            $election->votingThreshold()->updateOrCreate(
                ['election_id' => $election->id],
                [
                    'required_percentage' => $request->required_percentage,
                ]
            );
        }

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