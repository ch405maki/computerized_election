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
        // 1. Create the Voter
        $voter = Voter::create([
            'student_number' => $row['student_number'],
            'full_name'      => $row['full_name'],
            'student_year'   => $row['student_year'],
            'class_type'     => $row['class_type'],
            'sex'            => $row['sex'],
            'password'       => Hash::make($row['password']),
        ]);

        // 2. Call the separate function to handle the status
        $this->createVoterStatus($voter);

        return $voter;
    }

    private function createVoterStatus(Voter $voter)
    {
        VoterStatus::create([
            'voter_id'  => $voter->id,
            'activated' => true,
            'voted'     => false
        ]);
    }
}