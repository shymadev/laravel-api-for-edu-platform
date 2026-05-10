<?php

declare(strict_types=1);

namespace App\DTO\Review;

/**
 * Data Transfer Object for creating a new course review.
 */
readonly class CreateReviewDTO
{
    /**
     * @param int $userId
     * @param int $courseId
     * @param int $rating
     * @param string|null $reviewText
     */
    public function __construct(
        public int $userId,
        public int $courseId,
        public int $rating,
        public ?string $reviewText = null,
    ) {
    }

    /**
     * Converts the DTO to an associative array.
     *
     * @return array<string, int|string|null>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'course_id' => $this->courseId,
            'rating' => $this->rating,
            'review_text' => $this->reviewText,
        ];
    }
}
