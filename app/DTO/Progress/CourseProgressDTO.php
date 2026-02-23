<?php

declare(strict_types=1);

namespace App\DTO\Progress;

readonly class CourseProgressDTO
{
    public function __construct(
        public int $courseId,
        public string $title,
        public ?string $previewImageUrl,
        public int $totalLessons,
        public int $completedLessons,
        public float $progressPercentage,
        public bool $isCompleted,
    ) {
    }
}
