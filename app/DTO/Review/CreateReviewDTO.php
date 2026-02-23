<?php

declare(strict_types=1);

namespace App\DTO\Review;

class CreateReviewDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $courseId,
        public readonly int $rating,
        public readonly ?string $reviewText = null,
    ) {
    }

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
