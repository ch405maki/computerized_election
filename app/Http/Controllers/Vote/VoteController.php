<?php

namespace App\Http\Controllers\Vote;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Vote;
use App\Models\VoterStatus;
use App\Models\Election;
use App\Models\Position;
use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Auth;

use Inertia\Inertia;

class VoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $elections = Election::where('status', 'active')
            ->with(['candidates' => function($query) {
                $query->with('position');
            }])
            ->get();

        return Inertia::render('Vote/Index', [
            'elections' => $elections,
        ]);
    }

    public function votingPage()
    {
        $voter = Auth::guard('voter')->user();

        $elections = Election::where('status', 'active')
            ->with(['candidates' => function($query) {
                $query->with('position');
            }])
            ->get();

        return Inertia::render('Vote/Voting/Index', [
            'voter' => $voter,
            'elections' => $elections,
        ]);
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
        'votes' => 'required|array|min:1',
        'votes.*.candidate_id' => 'required|exists:candidates,id',
        'votes.*.election_id' => 'required|exists:elections,id',
    ]);

    DB::beginTransaction();

    try {
        $voterId = $validated['voter_id'];
        $electionId = $validated['votes'][0]['election_id'];

        // Basic validations
        $election = Election::findOrFail($electionId);
        $voterStatus = VoterStatus::where('voter_id', $voterId)->firstOrFail();
        
        if ($election->status !== 'active') {
            throw new \Exception('Election is not active.');
        }

        if (!$voterStatus->activated) {
            throw new \Exception('Voter account not activated.');
        }

        if (Vote::where('voter_id', $voterId)->where('election_id', $electionId)->exists()) {
            throw new \Exception('Already voted in this election.');
        }

        // Process votes
        $votes = [];
        $positionsVoted = [];

        foreach ($validated['votes'] as $voteData) {
            $candidate = Candidate::with('position')
                ->findOrFail($voteData['candidate_id']);

            // Check for duplicate positions
            if (in_array($candidate->position_id, $positionsVoted)) {
                throw new \Exception('Multiple votes for position: ' . $candidate->position->name);
            }
            $positionsVoted[] = $candidate->position_id;

            $votes[] = Vote::create([
                'voter_id' => $voterId,
                'candidate_id' => $voteData['candidate_id'],
                'election_id' => $electionId,
                'position_id' => $candidate->position_id
            ]);
        }

        // Update voter status
        $voterStatus->update(['voted' => true]);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Votes submitted successfully!',
            'data' => $votes
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException ? 404 : 400);
    }
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
