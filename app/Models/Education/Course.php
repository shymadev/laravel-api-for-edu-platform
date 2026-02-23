<?php

declare(strict_types=1);

namespace App\Models\Education;

use App\Models\User\UserCourseStatistics;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $table = 'courses';

    protected $fillable = [
        'language',
        'title',
        'description',
        'difficulty_level_id',
        'preview_image',
        'is_premium',
        'is_active',
    ];


    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
        'is_premium' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function difficultyLevel(): BelongsTo
    {
        return $this->belongsTo(DifficultyLevel::class, 'difficulty_level_id');
    }

    public function topics(): HasMany
    {
        return $this->hasMany(Topic::class, 'course_id');
    }

    public function userStats(): HasMany
    {
        return $this->hasMany(UserCourseStatistics::class, 'course_id');
    }
}
