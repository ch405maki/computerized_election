<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use Illuminate\Http\Request;

class CandidateController extends Controller
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
            'candidate_code' => 'required|string|unique:candidates|max:255',
            'position_id' => 'required|exists:positions,id',
            'candidate_name' => 'required|string|max:255',
            'candidate_party' => 'nullable|string|max:255',
            'candidate_picture' => 'nullable|string|max:255', // Store image path
        ]);

        $candidate = Candidate::create($validated);

        return response()->json([
            'message' => 'Candidate created successfully!',
            'candidate' => $candidate
        ], 201);
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
