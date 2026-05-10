<?php

declare(strict_types=1);

namespace App\Models\Education;

use App\Observers\TopicObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Topic (section) within a course; may nest under a parent topic.
 */
#[ObservedBy(TopicObserver::class)]
class Topic extends Model
{
    public $timestamps = false;

    protected $table = 'topics';

    protected $fillable = [
        'course_id',
        'parent_id',
        'title',
        'is_active',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Course this topic belongs to.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Parent topic, when this is a subtopic.
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Topic::class, 'parent_id');
    }

    /**
     * Child topics under this one.
     *
     * @return HasMany
     */
    public function subtopics(): HasMany
    {
        return $this->hasMany(Topic::class, 'parent_id');
    }

    /**
     * Lessons in this topic.
     *
     * @return HasMany
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'topic_id');
    }
}
