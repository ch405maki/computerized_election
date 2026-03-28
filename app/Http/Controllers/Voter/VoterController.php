<?php

namespace App\Http\Controllers\Voter;

use App\Http\Controllers\Controller;
use App\Models\Voter;
use App\Models\VoterStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Jobs\ProcessVoterImport;

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

            $query->where(function ($q) use ($search) {
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
        // Check if a trashed voter already exists with this student number
        $trashedVoter = Voter::onlyTrashed()
            ->where('student_number', $request->student_number)
            ->first();

        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                $trashedVoter ? Rule::unique('voters')->ignore($trashedVoter->id) : 'unique:voters'
            ],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255', // Made nullable just in case
            'student_year' => 'required|string',
            'sex' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        if ($trashedVoter) {
            // Restore and update the existing soft-deleted voter
            $trashedVoter->restore();
            $trashedVoter->update($validated);
            $voter = $trashedVoter;
            $message = 'Voter registered successfully!';
        } else {
            // Or create a brand new one
            $voter = Voter::create($validated);

            VoterStatus::create([
                'voter_id' => $voter->id,
                'activated' => true,
                'voted' => false
            ]);
            $message = 'Voter registered successfully!';
        }

        return response()->json([
            'message' => $message,
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
    public function update(Request $request, $id)
    {
        $voter = Voter::findOrFail($id);

        $validated = $request->validate([
            'student_number' => [
                'required',
                'string',
                Rule::unique('voters')->ignore($voter->id)
            ],
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'student_year' => 'required|string',
            'sex' => 'required|string',
            'password' => 'nullable|string|min:6',
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
            'data' => $voter->fresh()
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
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:10240',
            ]);

            $file = $request->file('file');
            $filePath = $file->store('temp_imports');
            $importId = uniqid();

            // Always use the queue for large files with PhpSpreadsheet to prevent timeouts
            Cache::put("import_status_{$importId}", 'processing', now()->addHours(1));

            // Dispatch the job
            ProcessVoterImport::dispatch($importId, $filePath);

            return response()->json([
                'queued' => true,
                'message' => 'Your file is being processed in the background.',
                'import_id' => $importId,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error uploading voters: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload file.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkImportStatus($importId)
    {
        $status = Cache::get("import_status_{$importId}", 'completed');
        return response()->json(['status' => $status]);
    }
}