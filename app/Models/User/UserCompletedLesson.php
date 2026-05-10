<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Education\Lesson;
use App\Observers\CompletedLessonObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records when a user has completed a lesson.
 */
#[ObservedBy(CompletedLessonObserver::class)]
class UserCompletedLesson extends Model
{
    public $timestamps = false;

    protected $table = 'user_completed_lessons';

    protected $fillable = [
        'user_id',
        'lesson_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Return the user this completed-lesson record belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Return the lesson this completed-lesson record belongs to.
     *
     * @return BelongsTo
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }
}
