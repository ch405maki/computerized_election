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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessVoterUpload;
use App\Jobs\ImportCompleted;

use Inertia\Inertia;

class VoterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Start the query
        $query = Voter::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            
            $query->where(function($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%"); // Added middle_name
            });
        }

        // Pagination with Query String
        $voters = $query->paginate(15)->withQueryString();

        return Inertia::render('Voters/Index', [
            'voters' => $voters, 
            
            // Pass the search term back to Vue as a prop
            'filters' => $request->only(['search']),
        ]);
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
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255', // Made nullable just in case
            'student_year'   => 'required|string',
            'sex'            => 'required|string',
            'password'       => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $voter = Voter::create($validated);

        // Create an INACTIVE voter status
        VoterStatus::create([
            'voter_id'  => $voter->id,
            'activated' => true,
            'voted'     => false
        ]);

        return response()->json([
            'message' => 'Voter registered successfully! Waiting for activation.',
            'voter'   => $voter
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
    public function update(Request $request, $id) 
    {
        $voter = Voter::findOrFail($id); 

        $validated = $request->validate([
            'student_number' => [
                'required', 'string',
                Rule::unique('voters')->ignore($voter->id)
            ],
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'student_year'   => 'required|string',
            'sex'            => 'required|string',
            'password'       => 'nullable|string|min:6',
        ]);

        // Check if the password was actually filled in request
        if (!empty($validated['password'])) {
            // Hash the new password
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Remove password from the array so it's not overwritten with null/empty
            unset($validated['password']);
        }

        $voter->update($validated);

        return response()->json([
            'message' => 'Voter updated successfully!',
            'data'    => $voter->fresh()
        ]);
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
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            ]);

            $file = $request->file('file');
            $filePath = $file->store('temp_imports'); 

            // 1. Generate a unique ID for this specific upload
            $importId = uniqid();

            if ($request->input('use_queue') === 'true') {
                
                // 2. Set the initial status in the cache
                Cache::put("import_status_{$importId}", 'processing', now()->addHours(1));

                // 3. Pass the ID to the import class so it knows which cache key to update later
                Excel::queueImport(new VoterImport($importId), $filePath);

                return response()->json([
                    'queued' => true,
                    'message' => 'Your file is being processed in the background.',
                    'import_id' => $importId, // 4. Send the ID to Vue
                ], 200);
            }

            // Fallback for immediate processing (small files)
            Excel::import(new VoterImport(), $filePath);

            return response()->json([
                'message' => 'Voters uploaded successfully!'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error uploading voters: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload file.',
                'error'   => $e->getMessage()
            ], 500);
        }

        if ($request->input('use_queue') === 'true') {
                
                // Set the initial status
                Cache::put("import_status_{$importId}", 'processing', now()->addHours(1));

                // Dispatch our custom wrapper job
                ProcessVoterUpload::dispatch($importId, $filePath);

                return response()->json([
                    'queued' => true,
                    'message' => 'Your file is being processed in the background.',
                    'import_id' => $importId, 
                ], 200);
            }
    }

    // 5. Create the endpoint that Vue will poll every 3 seconds
    public function checkImportStatus($importId)
    {
        // If the cache key is missing, assume it either finished or failed
        $status = Cache::get("import_status_{$importId}", 'completed'); 
        
        return response()->json(['status' => $status]);
    }
}