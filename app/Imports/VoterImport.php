<?php

namespace App\Imports;

use App\Models\Voter;
use App\Models\VoterStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VoterImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    // Process the file in chunks of 1000 to manage memory
    public function chunkSize(): int
    {
        return 3000;
    }

    public function collection(Collection $rows)
    {
        $votersToInsert = [];
        $studentNumbers = [];

        foreach ($rows as $row) {
            $studentNumber = trim($row['student_number'] ?? '');
            $firstName     = trim($row['first_name'] ?? '');
            $lastName      = trim($row['last_name'] ?? '');

            // Skip invalid rows locally
            if (empty($studentNumber) || empty($firstName) || empty($lastName)) {
                continue;
            }

            $password = $row['password'] ?? $studentNumber;

            // Prepare Voter Data Array
            $votersToInsert[] = [
                'student_number' => $studentNumber,
                'first_name'     => $firstName,
                'last_name'      => $lastName,
                'middle_name'    => !empty($row['middle_name']) ? trim($row['middle_name']) : null,
                'sex'            => trim($row['sex'] ?? 'Other'),
                'dob'            => !empty($row['dob']) ? trim($row['dob']) : null,
                'student_year'   => trim($row['student_year'] ?? ''),
                'password'       => Hash::make($password), // Still computationally heavy
                
            ];

            $studentNumbers[] = $studentNumber;
        }

        // Bulk Insert Voters
        if (!empty($votersToInsert)) {
            Voter::insert($votersToInsert);
        }

        // Retrieve IDs of inserted voters to link VoterStatus 
        $insertedVoters = Voter::whereIn('student_number', $studentNumbers)
            ->select('id', 'student_number')
            ->get();

        // Prepare VoterStatus Data
        $statusesToInsert = [];
        foreach ($insertedVoters as $voter) {
            $statusesToInsert[] = [
                'voter_id'   => $voter->id,
                'activated'  => true,
                'voted'      => false,
            ];
        }

        // Bulk Insert Statuses (1 Query)
        if (!empty($statusesToInsert)) {
            VoterStatus::insert($statusesToInsert);
        }
    }
}