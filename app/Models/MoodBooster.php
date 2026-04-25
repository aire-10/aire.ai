<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodBooster extends Model
{
    protected $table = 'mood_boosters';
    
    protected $fillable = [
        'user_id',
        'booster_type',
        'activity_name',
        'duration',
        'mood_before',
        'mood_after',
        'notes',
        'completed_at'
    ];
    
    protected $casts = [
        'completed_at' => 'datetime',
        'mood_before' => 'integer',
        'mood_after' => 'integer',
        'duration' => 'integer'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
