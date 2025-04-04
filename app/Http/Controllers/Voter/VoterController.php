<?php

    namespace App\Http\Controllers\Voter;

    use App\Http\Controllers\Controller;
    use App\Models\Voter;
    use App\Imports\VoterImport;
    use App\Models\VoterStatus;
    use Illuminate\Http\Request;
    use Maatwebsite\Excel\Facades\Excel;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Hash;

    use Inertia\Inertia;

    class VoterController extends Controller
    {
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $voters = Voter::latest()->get();

        return Inertia::render('Voters/Index', [ 'voters' => $voters ]);
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
        $voter = Voter::find($id);

        if (!$voter) {
            return response()->json(['error' => 'Voter not found'], 404);
        }

        $voter->delete();

        return response()->json(['message' => 'Voter deleted successfully'], 200);
    }

    public function uploadVoters(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
            ]);

            Excel::import(new VoterImport, $request->file('file'));

            return response()->json(['message' => 'Voters uploaded successfully!'], 200);
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database errors (e.g., duplicate entry)
            \Log::error('Database error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Duplicate entry found! Some records already exist.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Handle validation errors inside Excel import
            $failures = $e->failures();
            return response()->json([
                'message' => 'Some rows failed validation.',
                'errors' => $failures
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error uploading voters: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to upload file. Please check the format and data integrity.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
