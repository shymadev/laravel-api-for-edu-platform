<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Education\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Records when a user stopped (paused) a course.
 */
class UserStoppedCourse extends Model
{
    public $timestamps = false;

    protected $table = 'user_stopped_courses';

    protected $fillable = ['user_id', 'course_id', 'stopped_at'];

    /**
     * Return the user this stopped-course record belongs to.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Return the course this stopped-course record belongs to.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['stopped_at' => 'datetime'];
    }
}
