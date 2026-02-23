<?php

declare(strict_types=1);

namespace App\Models\User;

use App\Models\Education\Course;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCourseStatistics extends Model
{
    protected $table = 'user_course_statistics';

    protected $fillable = [
        'user_id',
        'course_id',
    ];

    public $timestamps = false;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
