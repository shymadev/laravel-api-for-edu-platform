<?php

declare(strict_types=1);

namespace App\Models\Additional;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'channel',
        'level',
        'message',
        'context',
        'user_id',
        'ip',
        'url',
        'method',
        'created_at',
    ];

    protected $casts = [
        'context' => 'array',
        'created_at' => 'datetime',
    ];
}
