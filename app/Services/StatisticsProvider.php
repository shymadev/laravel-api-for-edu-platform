<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\StatisticsDTO;
use App\Models\Education\Course;
use App\Models\Education\CourseReview;
use App\Models\User\User;
use App\Models\User\UserCompletedLesson;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Support\Facades\Cache;

#[Singleton]
class StatisticsProvider
{
    private const CACHE_KEY = 'platform.overall_statistics';
    private const CACHE_TTL = 600;

    /**
     * Get overall statistics for the platform.
     *
     * @return StatisticsDTO
     */
    public function getStatistics(): StatisticsDTO
    {
        return Cache::tags(['statistics'])
            ->remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return new StatisticsDTO(
                    activeStudents: User::where('is_blocked', false)->count(),
                    totalCourses: Course::where('is_active', true)->count(),
                    completedLessons: UserCompletedLesson::count(),
                    averageRating: round((float) CourseReview::avg('rating'), 2) ?? 0,
                );
            });
    }
}
