<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasFactory;

    protected $fillable = ['voter_id', 'candidate_id', 'election_id'];

    // Each vote belongs to a voter
    public function voter()
    {
        return $this->belongsTo(Voter::class, 'voter_id');
    }

    // Each vote is for one candidate
    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
    
    // Each vote belongs to an election
    public function election()
    {
        return $this->belongsTo(Election::class, 'election_id');
    }
}
