<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Election;
use Carbon\Carbon;
use App\Models\Vote;
use App\Models\Voter;
use Illuminate\Http\Request;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalVoters = Voter::count();
        $votesToday = Vote::whereDate('created_at', today())->count();
        $totalVotes = Vote::count();
        
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
            'recent_elections' => Election::withCount('votes')
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
            'participation_data' => Vote::selectRaw('DATE(created_at) as date, COUNT(*) as votes')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ]);
    }
}