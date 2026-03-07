<?php

namespace App\Imports;

use App\Models\Voter;
use App\Models\VoterStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache; // Required for status tracking
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithEvents; // Required for events
use Maatwebsite\Excel\Events\AfterImport; // Required for events

// Add WithEvents to the implemented interfaces
class VoterImport implements ToCollection, WithHeadingRow, WithChunkReading, ShouldQueue, WithEvents 
{
    private $importId;

    // Accept the import ID from the controller
    public function __construct($importId = null)
    {
        $this->importId = $importId;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function collection(Collection $rows)
    {
        $votersToInsert = [];
        $studentNumbers = [];

        foreach ($rows as $row) {
            $studentNumber = trim($row['student_number'] ?? '');
            $firstName     = trim($row['first_name'] ?? '');
            $lastName      = trim($row['last_name'] ?? '');

            if (empty($studentNumber) || empty($firstName) || empty($lastName)) {
                continue;
            }

            $password = $row['password'] ?? $studentNumber;

            $votersToInsert[] = [
                'student_number' => $studentNumber,
                'first_name'     => $firstName,
                'last_name'      => $lastName,
                'middle_name'    => !empty($row['middle_name']) ? trim($row['middle_name']) : null,
                'sex'            => trim($row['sex'] ?? 'Other'),
                'dob'            => !empty($row['dob']) ? trim($row['dob']) : null,
                'student_year'   => trim($row['student_year'] ?? ''),
                'password'       => Hash::make($password), 
                'created_at'     => now(),
                'updated_at'     => now(),
            ];

            $studentNumbers[] = $studentNumber;
        }

        if (!empty($votersToInsert)) {
            Voter::insert($votersToInsert);
        }

        $insertedVoters = Voter::whereIn('student_number', $studentNumbers)
            ->select('id', 'student_number')
            ->get();

        $statusesToInsert = [];
        foreach ($insertedVoters as $voter) {
            $statusesToInsert[] = [
                'voter_id'   => $voter->id,
                'activated'  => true,
                'voted'      => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($statusesToInsert)) {
            VoterStatus::insert($statusesToInsert);
        }
    }

    // THIS IS THE MAGIC: When the queue worker finishes, it updates the Cache
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function(AfterImport $event) {
                if ($this->importId) {

                    // Add this log to verify the event fires
                    \Illuminate\Support\Facades\Log::info("Excel import finished for ID: {$this->importId}");
                    // Tell the system this specific import is done
                    Cache::put("import_status_{$this->importId}", 'completed', now()->addHours(1));
                }
            },
        ];
    }
}