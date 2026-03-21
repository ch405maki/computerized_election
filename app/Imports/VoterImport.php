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
}