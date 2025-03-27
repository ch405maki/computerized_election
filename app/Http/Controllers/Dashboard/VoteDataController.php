<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\Candidate;
use App\Models\Position;
use Illuminate\Http\Request;

class VoteDataController extends Controller
{
    public function getVoteRanking()
    {
        $rankings = Vote::selectRaw('candidate_id, position_id, COUNT(*) as vote_count')
            ->groupBy('candidate_id', 'position_id')
            ->orderBy('position_id')
            ->orderByDesc('vote_count')
            ->get();

        $formattedRankings = $rankings->map(function ($rank) {
            $candidate = Candidate::find($rank->candidate_id);
            $position = Position::find($rank->position_id);

            return [
                'position' => $position ? $position->name : 'Unknown Position',
                'candidate' => $candidate ? $candidate->candidate_name : 'Unknown Candidate',
                'image' => $candidate ? $candidate->candidate_picture : null, // Ensure image is included
                'votes' => $rank->vote_count,
            ];
        });

        return response()->json([
            'message' => 'Vote rankings retrieved successfully',
            'rankings' => $formattedRankings,
        ]);
    }

}

