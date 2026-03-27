<?php

namespace App\Http\Controllers\Log;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Log;
use App\Models\User;
use App\Models\Voter;
use App\Models\Election;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class LogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $logs = Log::with(['user:id,name', 'voter:id'])->latest()->get();
        
        $currentElection = Election::withTrashed()->latest()->first();
        
        $canViewChart = false;
        $electionId = null;

        if ($currentElection) {
            $electionId = $currentElection->id;
            
            // Check if at least one voter has voted in this election
            $hasVotes = DB::table('votes')->where('election_id', $electionId)->exists();
            
            if (in_array($currentElection->status, ['active', 'completed']) && $hasVotes) {
                $canViewChart = true;
            }
        }
        
        return Inertia::render('Reports/Logs/Index', [
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'user_name' => $log->user?->name,
                    'voter_name' => $log->voter?->full_name,
                ];
            }),
            'currentElectionId' => $electionId,
            'canViewChart' => $canViewChart,
            'electionStatus' => $currentElection ? $currentElection->status : 'none',
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
        //
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

    public function getTurnoutByYear($electionId): JsonResponse
    {
        $turnout = DB::table('votes')
            ->join('voters', 'votes.voter_id', '=', 'voters.id')
            ->where('votes.election_id', $electionId)
            ->select(
                DB::raw('COALESCE(voters.student_year, "Unspecified") as year'),
                DB::raw('COUNT(DISTINCT votes.voter_id) as votes')
            )
            ->groupBy('year')
            ->orderBy('year')
            ->get();

        return response()->json($turnout);
    }
}
