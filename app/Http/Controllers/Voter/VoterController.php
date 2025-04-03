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
    
            $import = new VoterImport();
            Excel::import($import, $request->file('file'));
    
            $importedCount = $import->getRowCount();
    
            return response()->json([
                'message' => 'Voters imported successfully!',
                'imported_count' => $importedCount,
            ], 200);
    
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error during import: ' . $e->getMessage());
            return response()->json([
                'message' => 'Database error. Some records might already exist or data is invalid.',
                'error' => $e->getMessage(),
            ], 422);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = collect($failures)->map(function($failure) {
                return [
                    'row' => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors' => $failure->errors(),
                    'values' => $failure->values(),
                ];
            });
            
            Log::error('Validation errors during import: ', ['errors' => $errors->toArray()]);
            
            return response()->json([
                'message' => 'Validation failed for some rows.',
                'errors' => $errors,
                'error_count' => count($failures),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error during user import: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to import voters. Please check the file format and try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
