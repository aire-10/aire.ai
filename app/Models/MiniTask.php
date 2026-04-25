<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MiniTask extends Model
{
    protected $table = 'mini_tasks';
    
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'priority',
        'due_date',
        'order',
        'completed_at'
    ];
    
    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'order' => 'integer'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}