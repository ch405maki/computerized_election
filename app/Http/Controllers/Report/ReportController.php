<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Exports\ElectionResultsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Election;
use App\Models\Candidate;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        $elections = Election::withTrashed()
            ->withCount('votes')
            ->latest()
            ->get()
            ->map(function ($election) {
                return [
                    'id' => $election->id,
                    'name' => $election->name,
                    'status' => $election->status,
                    'start_date' => $election->start_date->format('Y-m-d'),
                    'end_date' => $election->end_date->format('Y-m-d'),
                    'votes_count' => $election->votes_count,
                ];
            });

        return Inertia::render('Reports/Results/Index', [
            'elections' => $elections
        ]);
    }

    // NEW: Verification Method
    public function verify(Request $request, Election $election)
    {
        $request->validate([
            // 'current_password' automatically checks against the logged-in user
            'password' => ['required', 'current_password'], 
        ]);

        // Set a session flag to authorize viewing this specific election
        session()->put("verified_election_{$election->id}", true);

        return redirect()->route('results.show', $election->id);
    }

    public function show(Election $election)
    {
        // Guard against direct URL access
        if (!session()->has("verified_election_{$election->id}")) {
            return redirect()->route('results.index')
                ->withErrors(['password' => 'Please verify your password to view these results.']);
        }

        // Get candidates with their position and vote count
        $candidates = Candidate::where('election_id', $election->id)
            ->with('position')
            ->withCount('votes')
            ->get();

        // Group by position name and format the output
        $positions = $candidates->groupBy(fn($candidate) => $candidate->position->name)
            ->map(function ($group) {
                return $group->map(function ($candidate) {
                    return [
                        'id' => $candidate->id,
                        'candidate_name' => $candidate->candidate_name,
                        'candidate_party' => $candidate->candidate_party,
                        'candidate_picture' => $candidate->candidate_picture,
                        'votes' => $candidate->votes_count,
                    ];
                });
            });

        return Inertia::render('Reports/Results/Show', [
            'election' => [
                'id' => $election->id,
                'name' => $election->name,
                'start_date' => $election->start_date->format('Y-m-d'),
                'end_date' => $election->end_date->format('Y-m-d'),
            ],
            'positions' => $positions,
        ]);
    }

    public function export(Election $election)
    {
        // You might want to add the session guard here too if exports should also be password protected!
        if (!session()->has("verified_election_{$election->id}")) {
            abort(403, 'Unauthorized action.');
        }

        return Excel::download(new ElectionResultsExport($election), 'election_results.xlsx');
    }

}