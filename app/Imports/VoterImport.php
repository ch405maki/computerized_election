<?php

namespace App\Imports;

use App\Models\Voter;
use App\Models\VoterStatus;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class VoterImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Get values from Excel row (use the column names in your Excel file)
        $studentNumber = $row['student_number'] ?? '';
        $firstName = $row['first_name'] ?? '';
        $lastName = $row['last_name'] ?? '';
        $middleName = $row['middle_name'] ?? null;
        $sex = $row['sex'] ?? 'Other';
        $dob = $row['dob'] ?? null;
        $studentYear = $row['student_year'] ?? '';
        $password = $row['password'] ?? $studentNumber; // Default to student_number if no password
        
        // Basic trim/clean
        $studentNumber = trim($studentNumber);
        $firstName = trim($firstName);
        $lastName = trim($lastName);
        
        // Skip if no student number or names
        if (empty($studentNumber) || empty($firstName) || empty($lastName)) {
            return null; // Skip this row
        }
        
        // Create the Voter
        $voter = Voter::create([
            'student_number' => $studentNumber,
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'middle_name'    => !empty($middleName) ? trim($middleName) : null,
            'student_year'   => trim($studentYear),
            'sex'            => trim($sex),
            'dob'            => !empty($dob) ? trim($dob) : null,
            'password'       => Hash::make($password),
        ]);

        // Create voter status
        VoterStatus::create([
            'voter_id'  => $voter->id,
            'activated' => true,
            'voted'     => false
        ]);

        return $voter;
    }
}