<?php

declare(strict_types=1);

namespace App\DTO\Progress;

/**
 * Data Transfer Object for aggregated user learning statistics.
 */
readonly class UserStatisticsDTO
{
    /**
     * @param int $totalCompletedLessons
     * @param int $totalCompletedCourses
     * @param int $totalInProgressCourses
     * @param float $averageProgress
     * @param int $totalCompletedExercises
     * @param int $totalListenedAudio
     */
    public function __construct(
        public int $totalCompletedLessons,
        public int $totalCompletedCourses,
        public int $totalInProgressCourses,
        public float $averageProgress,
        public int $totalCompletedExercises,
        public int $totalListenedAudio,
    ) {
    }
}
