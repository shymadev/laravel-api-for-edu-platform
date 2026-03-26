<?php

declare(strict_types=1);

namespace App\Models\Education;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoritePhrase extends Model
{
    protected $table = 'favorite_phrases';

    protected $fillable = [
        'user_id',
        'phrase_id',
        'is_learned',
    ];

    public $timestamps = false;

    protected $casts = [
        'added_at' => 'datetime',
        'is_learned' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function phrase(): BelongsTo
    {
        return $this->belongsTo(Phrase::class, 'phrase_id');
    }
}
