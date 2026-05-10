<?php

declare(strict_types=1);

namespace App\DTO\Progress;

/**
 * Data Transfer Object for course progress information.
 */
readonly class CourseProgressDTO
{
    /**
     * @param int $courseId
     * @param string $title
     * @param string|null $previewImageUrl
     * @param int $totalLessons
     * @param int $completedLessons
     * @param float $progressPercentage
     * @param bool $isCompleted
     * @param bool $isStopped
     */
    public function __construct(
        public int $courseId,
        public string $title,
        public ?string $previewImageUrl,
        public int $totalLessons,
        public int $completedLessons,
        public float $progressPercentage,
        public bool $isCompleted,
        public bool $isStopped = false,
    ) {
    }
}
