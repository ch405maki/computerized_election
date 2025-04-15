<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Exports\ElectionResultsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Election;
use App\Models\Candidate;
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
        $positions = Candidate::where('election_id', $election->id)
        ->with('position')
        ->get()
        ->groupBy(fn($candidate) => $candidate->position->name);

        return inertia('Reports/Results/Show', [
            'election' => $election,
            'positions' => $positions,
        ]);
    }

    public function export(Election $election)
    {
        return Excel::download(new ElectionResultsExport($election), 'election_results.xlsx');
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