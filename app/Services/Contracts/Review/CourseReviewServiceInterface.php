<?php

declare(strict_types=1);

namespace App\Services\Contracts\Review;

use App\DTO\Review\CreateReviewDTO;
use App\Models\Education\CourseReview;
use Illuminate\Database\Eloquent\Collection;

interface CourseReviewServiceInterface
{
    public function getReviewsByCourse(int $courseId): Collection;

    public function getCourseRating(int $courseId): array;

    public function createOrUpdateReview(CreateReviewDTO $dto): CourseReview;

    public function deleteReviewByUser(int $userId, int $courseId): bool;

    public function deleteReviewByAdmin(CourseReview $review): bool;
}
