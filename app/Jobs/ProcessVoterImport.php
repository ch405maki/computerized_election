<?php

namespace App\Jobs;

use App\Models\Voter;
use App\Models\VoterStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;

class ProcessVoterImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $importId;
    public $filePath;
    public $timeout = 600;

    public function __construct($importId, $filePath)
    {
        $this->importId = $importId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        try {
            $fullPath = Storage::path($this->filePath);
            $chunkSize = 1000;
            
            // We must define these outside the closure and pass them by reference (&$)
            $votersBatch = [];
            $studentNumbersBatch = [];

            // 1. Create the reader and stream the rows
            SimpleExcelReader::create($fullPath)
                // Normalize headers: trims spaces and makes them lowercase so "Student_Number" matches "student_number"
                ->formatHeadersUsing(fn(string $header) => strtolower(trim($header))) 
                ->getRows()
                ->each(function (array $rowProperties) use (&$votersBatch, &$studentNumbersBatch, $chunkSize) {
                    
                    $studentNumber = trim($rowProperties['student_number'] ?? '');
                    
                    // Skip if no student number exists for this row
                    if (empty($studentNumber)) {
                        return; // In a Laravel Collection ->each(), 'return' acts like 'continue'
                    }

                    $firstName = trim($rowProperties['first_name'] ?? '');
                    $lastName  = trim($rowProperties['last_name'] ?? '');

                    if (empty($firstName) || empty($lastName)) {
                        return; 
                    }

                    $rawPassword = !empty($rowProperties['password']) 
                        ? trim($rowProperties['password']) 
                        : $studentNumber;

                    $votersBatch[] = [
                        'student_number' => $studentNumber,
                        'first_name'     => $firstName,
                        'last_name'      => $lastName,
                        'middle_name'    => !empty($rowProperties['middle_name']) ? trim($rowProperties['middle_name']) : null,
                        'sex'            => trim($rowProperties['sex'] ?? 'Other'),
                        'dob'            => !empty($rowProperties['dob']) ? trim($rowProperties['dob']) : null,
                        'student_year'   => trim($rowProperties['student_year'] ?? ''),
                        
                        // OPTIMIZATION: Lower Bcrypt rounds to 8 for speed
                        'password'       => Hash::make($rawPassword, ['rounds' => 8]), 
                        
                        'created_at'     => now(),
                        'updated_at'     => now(),
                        'deleted_at'     => null,
                    ];

                    $studentNumbersBatch[] = $studentNumber;

                    // 2. Process Chunk when it hits 1000
                    if (count($votersBatch) >= $chunkSize) {
                        $this->processChunk($votersBatch, $studentNumbersBatch);
                        
                        // Reset batches
                        $votersBatch = [];
                        $studentNumbersBatch = [];
                    }
                });

            // 3. Process any remaining rows that didn't fill the last chunk
            if (!empty($votersBatch)) {
                $this->processChunk($votersBatch, $studentNumbersBatch);
            }

            // 4. Update the Cache so the Vue frontend knows it's done
            Cache::put("import_status_{$this->importId}", 'completed', now()->addHours(1));

        } catch (\Exception $e) {
            Log::error("Voter Import Failed [{$this->importId}]: " . $e->getMessage());
            Cache::put("import_status_{$this->importId}", 'failed', now()->addHours(1));
        } finally {
            // 5. Always clean up the temp file
            if (Storage::exists($this->filePath)) {
                Storage::delete($this->filePath);
            }
        }
    }

    /**
     * Helper method to handle database insertions via Upsert + Transactions
     */
    private function processChunk(array $votersBatch, array $studentNumbersBatch)
    {
        DB::transaction(function () use ($votersBatch, $studentNumbersBatch) {
            
            // Upsert Voters (Creates new ones, updates existing ones based on student_number)
            Voter::upsert(
                $votersBatch,
                ['student_number'], 
                ['first_name', 'last_name', 'middle_name', 'sex', 'dob', 'student_year', 'password', 'updated_at', 'deleted_at'] // Update these fields if student_number already exists
            );

            // Fetch the IDs of the voters we just inserted/updated
            $insertedVoters = Voter::whereIn('student_number', $studentNumbersBatch)->pluck('id');

            $statusesBatch = [];
            foreach ($insertedVoters as $id) {
                $statusesBatch[] = [
                    'voter_id'   => $id,
                    'activated'  => true,
                    'voted'      => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($statusesBatch)) {
                VoterStatus::upsert(
                    $statusesBatch, 
                    ['voter_id'], 
                    ['activated', 'voted', 'updated_at']
                );
            }
        });
    }
}