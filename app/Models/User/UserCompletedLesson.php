<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Education\Lesson;
use App\Observers\CompletedLessonObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(CompletedLessonObserver::class)]
class UserCompletedLesson extends Model
{
    protected $table = 'user_completed_lessons';

    protected $fillable = [
        'user_id',
        'lesson_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }
}
