<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage; // Correct import for Storage facade
use Illuminate\Support\Facades\Validator;
use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CandidateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Candidates/Index', [
            'candidates' => Candidate::with(['election', 'position'])
                ->latest()
                ->get(),
            'positions' => Position::all(),
            'elections' => Election::where('status', '!=', 'completed')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'election_id' => 'required|exists:elections,id',
            'position_id' => 'required|exists:positions,id',
            'candidate_code' => 'required|string|unique:candidates|max:10',
            'candidate_name' => 'required|string|max:255',
            'candidate_party' => 'nullable|string|max:255',
            'candidate_picture' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Handle file upload
        if ($request->hasFile('candidate_picture')) {
            $path = $request->file('candidate_picture')->store('candidates', 'public');
            $data['candidate_picture'] = $path;
        }

        $candidate = Candidate::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Candidate created successfully',
            'data' => $candidate->load(['election', 'position'])
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $candidate = Candidate::findOrFail($id);
        
        // Delete candidate picture from storage if it exists
        if ($candidate->candidate_picture) {
            Storage::disk('public')->delete($candidate->candidate_picture);
        }

        $candidate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Candidate deleted successfully'
        ]);
    }
}