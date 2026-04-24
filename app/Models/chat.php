<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    protected $table = 'chats';
    
    protected $fillable = [
        'user_id',
        'session_id',
        'message',
        'response',
        'is_user_message'
    ];
    
    protected $casts = [
        'is_user_message' => 'boolean'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}