<?php

declare(strict_types=1);

namespace App\DTO\Review;

class UpdateReviewDTO
{
    public function __construct(
        public readonly int $rating,
        public readonly ?string $reviewText = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'rating' => $this->rating,
            'review_text' => $this->reviewText,
        ];
    }
}
