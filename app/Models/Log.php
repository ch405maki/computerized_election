<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'voter_id', 'action', 'timestamp'];

    // Relationship: Log may belong to a User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship: Log may belong to a Voter
    public function voter()
    {
        return $this->belongsTo(Voter::class, 'voter_id');
    }
}
