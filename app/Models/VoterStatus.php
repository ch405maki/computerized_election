<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoterStatus extends Model
{
    use HasFactory;

    protected $fillable = ['voter_id', 'voted', 'activated'];

    // Relationship: Each status belongs to one voter
    public function voter()
    {
        return $this->belongsTo(Voter::class, 'voter_id');
    }
}
