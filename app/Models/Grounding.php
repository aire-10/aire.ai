<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grounding extends Model
{
    protected $table = 'groundings';
    
    protected $fillable = [
        'user_id',
        'exercise_type',
        'duration',
        'calm_level_before',
        'calm_level_after',
        'notes',
        'completed_steps',
        'total_steps',
        'is_completed',
        'progress',
        'completed_steps_json',
        'date',
        'completed_at'
    ];
    
    protected $casts = [
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'calm_level_before' => 'integer',
        'calm_level_after' => 'integer',
        'completed_steps' => 'integer',
        'total_steps' => 'integer',
        'progress' => 'array',
        'completed_steps_json' => 'array',
        'date' => 'date'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}