<?php

namespace App\Http\Controllers\Vote;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Vote;
use App\Models\VoterStatus;
use App\Models\Election;
use Illuminate\Http\Request;

use Inertia\Inertia;

class VoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Vote/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'voter_id' => 'required|exists:voters,id',
            'candidate_id' => 'required|exists:candidates,id',
            'election_id' => 'required|exists:elections,id',
        ]);

        // Check if the election is active
        $election = Election::find($validated['election_id']);
        if (!$election || $election->status !== 'active') {
            return response()->json(['message' => 'This election is not active.'], 403);
        }

        // Check if the voter is activated
        $voterStatus = VoterStatus::where('voter_id', $validated['voter_id'])->first();
        if (!$voterStatus || !$voterStatus->activated) {
            return response()->json(['message' => 'This voter is not activated. Cannot vote.'], 403);
        }

        // Check if the voter has already voted in this election
        $hasVoted = Vote::where('voter_id', $validated['voter_id'])
                        ->where('election_id', $validated['election_id'])
                        ->exists();
        if ($hasVoted) {
            return response()->json(['message' => 'This voter has already voted in this election.'], 403);
        }

        // Store the vote
        $vote = Vote::create($validated);

        // Mark the voter as voted
        $voterStatus->update(['voted' => true]);

        // Log the voting action
        Log::create([
            'voter_id' => $validated['voter_id'],
            'action' => 'Voted in election ' . $election->name,
        ]);

        return response()->json([
            'message' => 'Vote submitted successfully!',
            'vote' => $vote
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
