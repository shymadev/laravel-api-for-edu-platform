<?php

declare(strict_types=1);

namespace App\Models\Education;

use App\Observers\PhraseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Vocabulary / phrasebook entry.
 */
#[ObservedBy(PhraseObserver::class)]
class Phrase extends Model
{
    public $timestamps = true;

    protected $table = 'phrases';

    protected $fillable = [
        'text',
        'translation',
        'difficulty_level_id',
        'topic',
        'audio',
        'transcription',
        'is_phrasebook',
    ];

    protected $casts = [
        'audio' => 'string',
        'is_phrasebook' => 'boolean',
    ];

    /**
     * Difficulty this phrase is tagged with.
     *
     * @return BelongsTo
     */
    public function difficultyLevel(): BelongsTo
    {
        return $this->belongsTo(DifficultyLevel::class, 'difficulty_level_id');
    }

    /**
     * Users who saved this phrase to favorites.
     *
     * @return HasMany
     */
    public function favoritedBy(): HasMany
    {
        return $this->hasMany(FavoritePhrase::class, 'phrase_id');
    }

    /**
     * Computed absolute URL for the phrase audio file.
     *
     * @return Attribute
     */
    protected function audioUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->audio !== null
                ? asset('storage/' . $this->audio)
                : null,
        );
    }
}
