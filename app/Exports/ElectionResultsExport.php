<?php

namespace App\Exports;

use App\Models\Candidate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ElectionResultsExport implements FromCollection, WithHeadings
{
    protected $election;

    public function __construct($election)
    {
        $this->election = $election;
    }

    public function collection()
    {
        return Candidate::with('position')
        ->withCount('votes')
        ->where('election_id', $this->election->id)
        ->get()
        ->map(function ($candidate) {
            return [
                'Position' => $candidate->position->name,
                'Candidate Name' => $candidate->candidate_name,
                'Party' => $candidate->candidate_party ?? 'Independent',
                'Votes' => $candidate->votes_count ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return ['Position', 'Candidate Name', 'Party', 'Votes'];
    }
}
