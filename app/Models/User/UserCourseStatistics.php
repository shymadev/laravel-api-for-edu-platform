<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Education\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Aggregated per-user, per-course stats (e.g. progress).
 */
class UserCourseStatistics extends Model
{
    public $timestamps = false;

    protected $table = 'user_course_statistics';

    protected $fillable = [
        'user_id',
        'course_id',
    ];

    /**
     * User the statistics are for.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Course these statistics apply to.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
