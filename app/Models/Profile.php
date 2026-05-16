<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'nickname', 'avatar', 'bio', 'country', 'platforms', 'games'
    ];

    // Converte os campos JSON em arrays automaticamente[cite: 3]
    protected $casts = [
        'platforms' => 'array',
        'games' => 'array',
    ];
}