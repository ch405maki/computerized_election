<?php

namespace App\Http\Controllers\Users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;


use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }
    
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'role' => $validatedData['role'],
            'status' => $validatedData['status'],
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
    }

    public function uploadUsers(Request $request)
{
    try {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        $import = new UsersImport();
        Excel::import($import, $request->file('file'));

        $importedCount = $import->getRowCount();

        return response()->json([
            'message' => 'Users imported successfully!',
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
            'message' => 'Failed to import users. Please check the file format and try again.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'status' => 'required|string|in:active,inactive',
            ]);

            // Update user status
            $user->update($validated);

            return response()->json(['message' => 'User updated successfully!', 'user' => $user], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update user', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateStatus(Request $request, User $user)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'status' => 'required|string|in:active,inactive',
            ]);

            // Log the request data
            \Log::info('Updating user status:', [
                'user_id' => $user->id,
                'new_status' => $validated['status'],
            ]);

            // Update user status
            $user->update($validated);

            return response()->json(['message' => 'User status updated successfully!', 'user' => $user], 200);
        } catch (ValidationException $e) {
            \Log::error('Validation failed:', $e->errors());
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error('Failed to update user status:', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update user status', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
