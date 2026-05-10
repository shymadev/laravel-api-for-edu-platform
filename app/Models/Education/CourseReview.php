<?php

declare(strict_types=1);

namespace App\Models\Education;

use App\Models\User\User;
use App\Observers\ReviewObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User rating and text review for a course.
 */
#[ObservedBy(ReviewObserver::class)]
class CourseReview extends Model
{
    public $timestamps = true;

    protected $table = 'course_reviews';

    protected $fillable = [
        'user_id',
        'course_id',
        'rating',
        'review_text',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Author of the review.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Course being reviewed.
     *
     * @return BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
