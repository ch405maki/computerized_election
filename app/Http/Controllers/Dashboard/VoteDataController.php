<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\Election; // Make sure to import Election
use Illuminate\Http\Request;

class VoteDataController extends Controller
{
    public function getVoteRanking()
    {
        // 1. Get the currently active election
        $activeElection = Election::where('status', 'active')->first();

        // 2. If there's no active election, return empty rankings
        if (!$activeElection) {
            return response()->json([
                'message' => 'No active election found',
                'rankings' => [],
            ]);
        }

        // 3. Filter votes specifically for the active election
        $rankings = Vote::with(['candidate', 'position'])
            ->where('election_id', $activeElection->id) // <-- Added this filter!
            ->selectRaw('candidate_id, position_id, COUNT(*) as vote_count')
            ->groupBy('candidate_id', 'position_id')
            ->orderBy('position_id')
            ->orderByDesc('vote_count')
            ->get();

        $formattedRankings = $rankings->map(function ($rank) {
            return [
                'position' => $rank->position ? $rank->position->name : 'Unknown Position',
                'candidate' => $rank->candidate ? $rank->candidate->candidate_name : 'Unknown Candidate',
                'party' => $rank->candidate ? $rank->candidate->candidate_party : 'Unknown Party',
                'image' => $rank->candidate ? $rank->candidate->candidate_picture : null,
                'votes' => $rank->vote_count,
            ];
        });

        return response()->json([
            'message' => 'Vote rankings retrieved successfully',
            'rankings' => $formattedRankings,
        ]);
    }
}