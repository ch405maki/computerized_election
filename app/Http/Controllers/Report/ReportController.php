<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Exports\ElectionResultsExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Election;
use App\Models\Candidate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

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

    public function verify(Request $request, Election $election)
    {
        $request->validate([
            'password' => ['required', 'current_password'], 
        ]);

        session()->put("verified_election_{$election->id}", true);

        return redirect()->route('results.show', $election->id);
    }

    public function show(Election $election)
    {
        if (!session()->has("verified_election_{$election->id}")) {
            return redirect()->route('results.index')
                ->withErrors(['password' => 'Please verify your password to view these results.']);
        }

        $candidates = Candidate::where('election_id', $election->id)
            ->with('position')
            ->withCount('votes')
            ->get();

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

        $signature = auth()->user()->signature;
        $signatureUrl = $signature ? asset('storage/' . $signature->file_path) : null;

        return Inertia::render('Reports/Results/Show', [
            'election' => [
                'id' => $election->id,
                'name' => $election->name,
                'start_date' => $election->start_date->format('Y-m-d'),
                'end_date' => $election->end_date->format('Y-m-d'),
            ],
            'positions' => $positions,
            'signatureUrl' => $signatureUrl,
        ]);
    }

    public function export(Election $election)
    {
        if (!session()->has("verified_election_{$election->id}")) {
            abort(403, 'Unauthorized action.');
        }

        return Excel::download(new ElectionResultsExport($election), 'election_results.xlsx');
    }

    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => ['required', 'image', 'mimes:png', 'max:2048'],
        ]);

        $user = $request->user();
        $file = $request->file('signature');

        $path = $file->store('signatures', 'public');

        if ($user->signature && Storage::disk('public')->exists($user->signature->file_path)) {
            Storage::disk('public')->delete($user->signature->file_path);
        }

        $user->signature()->updateOrCreate(
            ['user_id' => $user->id],
            ['file_path' => $path]
        );

        return back()->with('success', 'E-Signature uploaded successfully.');
    }

    public function destroySignature(Request $request)
    {
        $user = $request->user();

        if ($user->signature) {
            if (Storage::disk('public')->exists($user->signature->file_path)) {
                Storage::disk('public')->delete($user->signature->file_path);
            }
            
            $user->signature()->delete();
        }

        return back()->with('success', 'E-Signature removed successfully.');
    }

}