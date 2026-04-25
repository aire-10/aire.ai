<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowthMetric extends Model
{
    protected $table = 'growth_metrics';
    
    protected $fillable = [
        'user_id',
        'date',
        'self_reflection',
        'resilience_score',
        'mindfulness_practice',
        'goals_achieved',
        'notes'
    ];
    
    protected $casts = [
        'date' => 'date',
        'mindfulness_practice' => 'boolean',
        'goals_achieved' => 'integer',
        'self_reflection' => 'integer',
        'resilience_score' => 'integer'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}