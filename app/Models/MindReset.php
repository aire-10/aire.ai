<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MindReset extends Model
{
    protected $table = 'mind_resets';
    
    protected $fillable = [
        'user_id',
        'technique',
        'duration',
        'stress_before',
        'stress_after',
        'notes',
        'started_at',
        'completed_at',
        'completed'
    ];
    
    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'completed' => 'boolean',
        'stress_before' => 'integer',
        'stress_after' => 'integer',
        'duration' => 'integer'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}