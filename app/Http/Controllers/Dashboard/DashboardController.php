<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Carbon\Carbon;
use App\Models\Log;
use App\Models\User;
use App\Models\Voter;
use App\Models\Vote;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Total active voters (Laravel automatically hides deleted ones here)
        $totalVoters = Voter::count();
        $votesToday = Vote::whereDate('created_at', today())->distinct('voter_id')->count('voter_id');

        // We can do this directly in the database instead:
        $totalVotes = Vote::distinct('voter_id')->count('voter_id');

        $logs = Log::with([
    'user' => function($query) { 
        $query->select('id', 'name'); 
    }, 
    'voter' => function($query) { 
        $query->withTrashed()->select('id', 'student_number'); 
    }
])->latest()->get();
        
        return Inertia::render('Dashboard', [
            'stats' => [
                // Added withTrashed() to get the true total of all past elections.
                'total_elections' => Election::withTrashed()->count(),
                
                'active_elections' => Election::where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->count(),
                'total_voters' => $totalVoters,
                'votes_today' => $votesToday,
                'participation_rate' => $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 2) : 0,
            ],
            
            // Added withTrashed() to fetch archived elections
            'recent_elections' => Election::withTrashed()
                ->withCount([
                    'votes as votes_count' => function ($query) {
                        $query->select(DB::raw('COUNT(DISTINCT voter_id)'));
                    }
                ])
                ->orderBy('start_date', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($election) {
                    return [
                        'id' => $election->id,
                        'name' => $election->name,
                        'start_date' => Carbon::parse($election->start_date)->format('Y-m-d'),
                        'end_date' => Carbon::parse($election->end_date)->format('Y-m-d'),
                        'votes_count' => $election->votes_count,
                        'deleted_at' => $election->deleted_at ? $election->deleted_at->toIso8601String() : null,
                    ];
                }),
                
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'user_name' => $log->user?->name,
                    'student_number' => $log->voter?->student_number,
                ];
            }),
            'participation_data' => Vote::selectRaw('DATE(created_at) as date, COUNT(*) as votes')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ]);
    }
}