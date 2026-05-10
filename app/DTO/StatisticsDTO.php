<?php

declare(strict_types=1);

namespace App\DTO;

/**
 * Data Transfer Object for statistics data.
 */
final readonly class StatisticsDTO
{
    /**
     * Constructs a new instance of the StatisticsDTO class.
     *
     * @param int $activeStudents
     * @param int $totalCourses
     * @param int $completedLessons
     * @param float $averageRating
     */
    public function __construct(
        public int $activeStudents,
        public int $totalCourses,
        public int $completedLessons,
        public float $averageRating,
    ) {
    }

    /**
     * Converts the DTO to an associative array.
     *
     * @return array<string, int|float>
     */
    public function toArray(): array
    {
        return [
            'active_students' => $this->activeStudents,
            'total_courses' => $this->totalCourses,
            'completed_lessons' => $this->completedLessons,
            'average_rating' => $this->averageRating,
        ];
    }
}
