<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Election extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 
        'start_date', 
        'end_date', 
        'voting_start_time', 
        'voting_end_time', 
        'status'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'voting_start_time' => 'datetime',
        'voting_end_time' => 'datetime',
    ];

    public function votingThreshold()
    {
        return $this->hasOne(VotingThreshold::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}