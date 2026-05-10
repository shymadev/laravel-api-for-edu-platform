<?php

declare(strict_types=1);

namespace App\Models\Education;

use App\Models\Education\Paragraphs\BaseParagraph;
use App\Models\User\UserCompletedLesson;
use App\Observers\LessonObserver;
use App\Services\Education\ParagraphParser;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lesson in a course topic, with block-based content.
 */
#[ObservedBy(LessonObserver::class)]
class Lesson extends Model
{
    public $timestamps = false;

    protected $table = 'lessons';

    protected $fillable = [
        'topic_id',
        'title',
        'weight',
        'content',
        'is_active',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Topic that contains this lesson.
     *
     * @return BelongsTo
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    /**
     * Completion records for this lesson.
     *
     * @return HasMany
     */
    public function completedBy(): HasMany
    {
        return $this->hasMany(UserCompletedLesson::class, 'lesson_id');
    }

    /**
     * Get parsed paragraphs from content.
     *
     * @return BaseParagraph[]
     */
    protected function paragraphs(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->content === null || $this->content === []) {
                    return [];
                }

                return ParagraphParser::parse($this->content);
            },
        );
    }
}
