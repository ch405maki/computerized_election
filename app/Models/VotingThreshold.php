<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VotingThreshold extends Model
{
    use HasFactory;

    protected $fillable = [
        'election_id',
        'required_percentage',
    ];

    protected $casts = [
        'required_percentage' => 'float',
    ];

    public function election()
    {
        return $this->belongsTo(Election::class);
    }
}