<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Voter extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'voters';

    protected $fillable = [
        'student_number',
        'full_name',
        'student_year',
        'class_type',
        'sex',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

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
