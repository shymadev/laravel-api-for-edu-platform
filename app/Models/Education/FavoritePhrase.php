<?php

declare(strict_types=1);

namespace App\Models\Education;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User’s saved (favorite) phrase.
 */
class FavoritePhrase extends Model
{
    public $timestamps = false;

    protected $table = 'favorite_phrases';

    protected $fillable = [
        'user_id',
        'phrase_id',
        'is_learned',
    ];

    protected $casts = [
        'added_at' => 'datetime',
        'is_learned' => 'boolean',
    ];

    /**
     * User who favorited the phrase.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Favorited phrase.
     *
     * @return BelongsTo
     */
    public function phrase(): BelongsTo
    {
        return $this->belongsTo(Phrase::class, 'phrase_id');
    }
}
