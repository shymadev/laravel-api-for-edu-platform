<?php

declare(strict_types=1);

namespace App\Services\Contracts\Progress;

use App\DTO\Progress\CourseProgressDTO;
use App\DTO\Progress\UserStatisticsDTO;
use Illuminate\Support\Collection;

interface CourseProgressServiceInterface
{
    /**
     * Get a list of lessons completed by the user.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function completedLessons(int $userId): Collection;

    /**
     * Mark a lesson as complete for the user.
     *
     * @param int $userId
     * @param int $lessonId
     *
     * @return bool true if marked newly complete, false if already completed
     */
    public function markLessonComplete(int $userId, int $lessonId): bool;

    /**
     * Calculate the user's progress for a specific course.
     *
     * @param int $userId
     * @param int $courseId
     *
     * @return CourseProgressDTO
     */
    public function courseProgress(int $userId, int $courseId): CourseProgressDTO;

    /**
     * Get progress for all courses the user has started (or all available courses).
     *
     * @param int $userId
     *
     * @return Collection<int, CourseProgressDTO>
     */
    public function userCoursesProgress(int $userId): Collection;

    /**
     * Get overall statistics for the user.
     *
     * @param int $userId
     *
     * @return UserStatisticsDTO
     */
    public function userStatistics(int $userId): UserStatisticsDTO;
}
