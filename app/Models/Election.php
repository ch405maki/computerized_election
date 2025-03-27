<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    // Relationship: An election has many votes
    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    // Relationship: An election has many candidates
    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
