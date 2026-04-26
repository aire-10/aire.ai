<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    protected $table = 'chat_sessions';
    
    protected $fillable = [
        'session_id',
        'user_id',
        'title'
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function chats(): HasMany
    {
        return $this->hasMany(Chat::class, 'session_id', 'session_id');
    }
    
    // Get the last message preview for this session
    public function getPreviewAttribute()
    {
        $lastMessage = $this->chats()
            ->where('is_user_message', true)
            ->latest()
            ->first();
            
        if ($lastMessage) {
            $preview = $lastMessage->message;
            return strlen($preview) > 60 ? substr($preview, 0, 57) . '...' : $preview;
        }
        
        return 'No messages yet...';
    }
    
    // Get the last activity time
    public function getLastActivityAttribute()
    {
        $lastChat = $this->chats()->latest()->first();
        return $lastChat ? $lastChat->created_at : $this->updated_at;
    }
}