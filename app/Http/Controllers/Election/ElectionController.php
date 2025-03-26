<?php

namespace App\Http\Controllers\Election;

use App\Models\Election;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Inertia\Inertia;

class ElectionController extends Controller
{

    public function index()
    {
        $elections = Election::latest()->get();
        return Inertia::render('Elections/Index', [
            'elections' => $elections
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:active,completed,upcoming',
        ]);

        $election = Election::create($validated);

        return response()->json(['message' => 'Election created successfully!', 'election' => $election], 201);
    }

    public function update(Request $request, Election $election)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|in:active,completed,upcoming',
        ]);

        $election->update($validated);

        return response()->json(['message' => 'Election updated successfully!', 'election' => $election]);
    }

    public function destroy(Election $election)
    {
        $election->delete();

        return response()->json(['message' => 'Election deleted successfully!']);
    }
}
