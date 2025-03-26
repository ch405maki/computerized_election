<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\VoterStatus;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoterStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $validated = $request->validate([
            'voter_id' => 'required|exists:voters,id',
            'voted' => 'required|boolean',
        ]);

        $voterStatus = VoterStatus::updateOrCreate(
            ['voter_id' => $validated['voter_id']],
            ['voted' => $validated['voted']]
        );

        return response()->json([
            'message' => 'Voter status updated successfully!',
            'voter_status' => $voterStatus
        ], 201);
    }

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
}
