<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mood extends Model
{
    protected $fillable = [
        'user_id',
        'mood_level',
        'notes',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
        'mood_level' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}