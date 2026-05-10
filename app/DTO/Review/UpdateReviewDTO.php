<?php

declare(strict_types=1);

namespace App\DTO\Review;

/**
 * Data Transfer Object for updating an existing course review.
 */
readonly class UpdateReviewDTO
{
    /**
     * @param int $rating
     * @param string|null $reviewText
     */
    public function __construct(
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
            'rating' => $this->rating,
            'review_text' => $this->reviewText,
        ];
    }
}
