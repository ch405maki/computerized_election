<?php

namespace App\Imports;

use App\Models\Voter;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class VoterImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Voter([
            'student_number'   => $row['student_number'],
            'full_name'        => $row['full_name'],
            'student_year'     => $row['student_year'],
            'class_type'       => $row['class_type'],
            'sex'              => $row['sex'],
            'password'         => Hash::make($row['password']),
        ]);
    }
}
