<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Phrase extends Model
{
    protected $table = 'phrases';

    protected $fillable = [
        'text',
        'translation',
        'difficulty_level_id',
        'topic',
        'audio',
    ];

    protected $casts = [
        'audio' => 'string',
    ];

    public $timestamps = true;

    public function difficultyLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(DifficultyLevel::class, 'difficulty_level_id');
    }

    /**
     * Get the full URL for the audio file.
     */
    public function favoritedBy(): HasMany
    {
        return $this->hasMany(FavoritePhrase::class, 'phrase_id');
    }

    protected function audioUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->audio !== null
                ? asset('storage/' . $this->audio)
                : null,
        );
    }
}
