<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'candidate_code',
        'election_id',
        'position_id',
        'candidate_name',
        'candidate_party',
        'candidate_picture',
    ];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
    

    public function election()
    {
        return $this->belongsTo(Election::class)->withTrashed();
    }


    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}