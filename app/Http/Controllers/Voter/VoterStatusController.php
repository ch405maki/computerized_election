<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Voter;
use App\Models\VoterStatus;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VoterStatusController extends Controller
{
    /**
     * Display inactive voters
     */
    public function index(Request $request)
    {
        $query = VoterStatus::with('voter')
            ->where('activated', false);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('voter', function($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                ->orWhere('full_name', 'like', "%{$search}%")
                ->orWhere('student_year', 'like', "%{$search}%")
                ->orWhere('class_type', 'like', "%{$search}%");
            });
        }

        $inactiveVoters = $query->get()
            ->map(function ($status) {
                return [
                    'id' => $status->voter_id,
                    'student_number' => $status->voter->student_number,
                    'full_name' => $status->voter->full_name,
                    'student_year' => $status->voter->student_year,
                    'class_type' => $status->voter->class_type,
                    'created_at' => $status->voter->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Voters/Status/Index', [
            'inactiveVoters' => $inactiveVoters
        ]);
    }

    /**
     * Activate a voter
     */
    public function activate($id)
    {
        $voterStatus = VoterStatus::where('voter_id', $id)->first();

        if (!$voterStatus) {
            return response()->json(['message' => 'Voter status not found'], 404);
        }

        if ($voterStatus->activated) {
            return response()->json(['message' => 'Voter is already activated'], 400);
        }

        // Activate the voter
        $voterStatus->update(['activated' => true]);

        // Log the activation
        Log::create([
            'user_id' => Auth::id(), 
            'voter_id' => $id,
            'action' => 'Activated voter'
        ]);

        return response()->json(['message' => 'Voter activated successfully']);
    }

    /**
     * Activate all inactive voters
     */
    public function activateAll()
    {
        $inactiveVoters = VoterStatus::where('activated', false)->get();

        if ($inactiveVoters->isEmpty()) {
            return response()->json(['message' => 'No inactive voters found'], 404);
        }

        $activatedCount = 0;

        foreach ($inactiveVoters as $voterStatus) {
            $voterStatus->update(['activated' => true]);
            
            Log::create([
                'user_id' => Auth::id(),
                'voter_id' => $voterStatus->voter_id,
                'action' => 'Activated voter (bulk)'
            ]);
            
            $activatedCount++;
        }

        return response()->json([
            'message' => "Successfully activated {$activatedCount} voters",
            'count' => $activatedCount
        ]);
    }
}