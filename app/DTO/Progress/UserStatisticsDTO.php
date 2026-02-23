<?php

declare(strict_types=1);

namespace App\DTO\Progress;

readonly class UserStatisticsDTO
{
    public function __construct(
        public int $totalCompletedLessons,
        public int $totalCompletedCourses,
        public int $totalInProgressCourses,
        public float $averageProgress,
    ) {
    }
}
