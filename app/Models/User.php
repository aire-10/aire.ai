<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',

        'moodlifting' => 'array',
        'mindreset' => 'array',
        'minitask' => 'array',
        'bodybooster' => 'array',
    ];

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    public function moods()
    {
        return $this->hasMany(Mood::class);
    }

    public function breathingSessions()
    {
        return $this->hasMany(BreathingSession::class);
    }

    public function groundings()
    {
        return $this->hasMany(Grounding::class);
    }

    public function moodBoosters()
    {
        return $this->hasMany(MoodBooster::class);
    }

    public function miniTasks()
    {
        return $this->hasMany(MiniTask::class);
    }

    public function mindResets()
    {
        return $this->hasMany(MindReset::class);
    }
}