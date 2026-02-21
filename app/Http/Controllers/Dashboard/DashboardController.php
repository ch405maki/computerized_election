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
        $totalVoters = Voter::count();
        $votesToday = Vote::whereDate('created_at', today())->distinct('voter_id')->count('voter_id');

        $votes = Vote::all();
        $uniqueVoters = $votes->unique('voter_id');
        $totalVotes = $uniqueVoters->count();

        $logs = Log::with(['user:id,name', 'voter:id'])->latest()->get();
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_elections' => Election::count(),
                'active_elections' => Election::where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->count(),
                'total_voters' => $totalVoters,
                'votes_today' => $votesToday,
                'participation_rate' => $totalVoters > 0 ? round(($totalVotes / $totalVoters) * 100, 2) : 0,
            ],
            'recent_elections' => Election::withCount([
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
                    'start_date' => \Carbon\Carbon::parse($election->start_date)->format('Y-m-d'),
                    'end_date' => \Carbon\Carbon::parse($election->end_date)->format('Y-m-d'),
                    'votes_count' => $election->votes_count,
                ];
            }),
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                    'user_name' => $log->user?->name,
                    'voter_name' => $log->voter?->full_name,
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