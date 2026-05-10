<?php

declare(strict_types=1);

namespace App\Models\Additional;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Application log entry (channel, level, message, context).
 */
class Log extends Model
{
    public $timestamps = false;

    protected $table = 'logs';

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

    /**
     * User associated with the log entry, if any.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
