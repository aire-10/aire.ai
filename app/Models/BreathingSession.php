<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreathingSession extends Model
{
    protected $fillable = [
        'user_id',
        'duration'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}