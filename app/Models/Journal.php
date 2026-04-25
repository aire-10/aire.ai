<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'user_id',
        'content',
        'image'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}