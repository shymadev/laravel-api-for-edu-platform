<?php

namespace App\Models\Education;

use App\Models\User\User;
use App\Observers\ReviewObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
#[ObservedBy(ReviewObserver::class)]
class CourseReview extends Model
{
    protected $table = 'course_reviews';

    protected $fillable = [
        'user_id',
        'course_id',
        'rating',
        'review_text',
    ];

    public $timestamps = true;

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
