<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    // Relationship: A position has many candidates
    public function candidates()
    {
        return $this->hasMany(Candidate::class, 'position_id');
    }

    public function election()
    {
        return $this->belongsTo(Election::class);
    }

}
