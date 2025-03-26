<?php

    namespace App\Http\Controllers\Voter;

    use App\Http\Controllers\Controller;
    use App\Models\Voter;
    use App\Models\VoterStatus;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;

    class VoterController extends Controller
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
            'student_number' => 'required|string|unique:voters',
            'full_name' => 'required|string|max:255',
            'student_year' => 'required|string',
            'class_type' => 'required|string',
            'sex' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        
        $voter = Voter::create($validated);

        // Create an INACTIVE voter status
        VoterStatus::create([
            'voter_id' => $voter->id,
            'activated' => false,
            'voted' => false
        ]);

        return response()->json([
            'message' => 'Voter registered successfully! Waiting for activation.',
            'voter' => $voter
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
