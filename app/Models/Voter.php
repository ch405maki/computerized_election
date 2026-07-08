<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Voter extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'voters';

    protected $fillable = [
        'student_number',
        'last_name',
        'first_name',
        'middle_name',
        'sex',
        'dob',
        'student_year',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    protected $appends = ['full_name'];

    // Computed full_name
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . 
               ($this->middle_name ? $this->middle_name . ' ' : '') . 
               $this->last_name);
    }

    // Checks if the voter has already voted in the current active election
    public function hasVotedInElection($electionId)
    {
        return $this->votes()->where('election_id', $electionId)->exists();
    }

    // Relationship with Votes
    public function votes()
    {
        return $this->hasMany(Vote::class, 'voter_id');
    }

    public function hasVoted(): bool
    {
        return $this->votes()->exists();
    }

    // Relationship with VoterStatus
    public function status()
    {
        return $this->hasOne(VoterStatus::class, 'voter_id');
    }

    // Relationship: Voter has many logs
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class, 'voter_id');
    }
}
