<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    protected $fillable = [
        'user_id',
        'points',
        'streak',
        'stage'
    ];
}
