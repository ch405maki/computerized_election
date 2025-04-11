<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Election;
use App\Models\Candidate;
use App\Models\Position;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {   
        $elections = Election::withCount('votes')
            ->latest()
            ->get()
            ->map(function ($election) {
                return [
                    'id' => $election->id,
                    'name' => $election->name,
                    'status' => $this->getElectionStatus($election),
                    'start_date' => $election->start_date->format('Y-m-d'),
                    'end_date' => $election->end_date->format('Y-m-d'),
                    'votes_count' => $election->votes_count,
                ];
            });

        return Inertia::render('Reports/Results/Index', [
            'elections' => $elections
        ]);
    }

    public function show(Election $election)
    {
        $positions = Position::whereHas('candidates', function ($query) use ($election) {
                $query->where('election_id', $election->id);
            })
            ->with(['candidates' => function ($query) use ($election) {
                $query->where('election_id', $election->id)
                    ->withCount('votes')
                    ->orderBy('votes_count', 'desc');
            }])
            ->get()
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'name' => $position->name,
                    'candidates' => $position->candidates->map(function ($candidate) {
                        return [
                            'id' => $candidate->id,
                            'name' => $candidate->candidate_name,
                            'party' => $candidate->candidate_party,
                            'photo' => $candidate->candidate_picture,
                            'votes_count' => $candidate->votes_count,
                        ];
                    })->toArray(), // Ensure this is converted to array
                ];
            })
            ->toArray(); // Convert to array for Inertia

        return Inertia::render('Reports/Results/Show', [
            'election' => [
                'id' => $election->id,
                'name' => $election->name,
                'start_date' => $election->start_date->format('Y-m-d'),
                'end_date' => $election->end_date->format('Y-m-d'),
            ],
            'positions' => $positions ?: [], // Ensure positions is always an array
        ]);
    }

    private function getElectionStatus(Election $election): string
    {
        $now = now();
        $start = $election->start_date;
        $end = $election->end_date;

        if ($now < $start) return 'upcoming';
        if ($now > $end) return 'completed';
        return 'active';
    }
}