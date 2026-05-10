<?php

declare(strict_types=1);

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Course/phrase difficulty tier (e.g. A1, B2).
 */
class DifficultyLevel extends Model
{
    public $timestamps = false;

    protected $table = 'difficulty_levels';

    protected $fillable = [
        'name',
        'value',
        'description',
    ];

    /**
     * Courses at this difficulty.
     *
     * @return HasMany
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'difficulty_level_id');
    }

    /**
     * Phrases at this difficulty.
     *
     * @return HasMany
     */
    public function phrases(): HasMany
    {
        return $this->hasMany(Phrase::class, 'difficulty_level_id');
    }
}
