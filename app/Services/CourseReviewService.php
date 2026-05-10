<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\Review\CreateReviewDTO;
use App\Models\Education\CourseReview;
use Illuminate\Container\Attributes\Singleton;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Service for managing course reviews.
 */
#[Singleton]
class CourseReviewService
{
    /**
     * Get reviews by course ID.
     *
     * @param int $courseId
     *
     * @return Collection<int, CourseReview>
     */
    public function getReviewsByCourse(int $courseId): Collection
    {
        return Cache::tags(['reviews', "course.{$courseId}"])->remember(
            "reviews.course.{$courseId}",
            900,
            fn () => CourseReview::with('user.profile')
                ->where('course_id', $courseId)
                ->orderBy('created_at', 'desc')
                ->get(),
        );
    }

    /**
     * Get course rating.
     *
     * @param int $courseId
     *
     * @return array<string, mixed>
     */
    public function getCourseRating(int $courseId): array
    {
        return Cache::tags(['reviews', "course.{$courseId}"])->remember(
            "rating.course.{$courseId}",
            900,
            function () use ($courseId): array {
                $reviews = CourseReview::where('course_id', $courseId)->get();
                $count = $reviews->count();

                if ($count === 0) {
                    return [
                        'average_rating' => 0.0,
                        'total_reviews' => 0,
                        'rating_distribution' => [
                            '5' => 0,
                            '4' => 0,
                            '3' => 0,
                            '2' => 0,
                            '1' => 0,
                            '0' => 0,
                        ],
                    ];
                }

                $totalRating = $reviews->sum('rating');
                $distribution = $reviews->groupBy('rating')->map->count()->toArray();

                return [
                    'average_rating' => round($totalRating / $count, 2),
                    'total_reviews' => $count,
                    'rating_distribution' => [
                        '5' => $distribution[5] ?? 0,
                        '4' => $distribution[4] ?? 0,
                        '3' => $distribution[3] ?? 0,
                        '2' => $distribution[2] ?? 0,
                        '1' => $distribution[1] ?? 0,
                        '0' => $distribution[0] ?? 0,
                    ],
                ];
            },
        );
    }

    /**
     * Create or update a review.
     *
     * @param CreateReviewDTO $dto
     *
     * @return CourseReview
     */
    public function createOrUpdateReview(CreateReviewDTO $dto): CourseReview
    {
        return CourseReview::updateOrCreate(
            [
                'user_id' => $dto->userId,
                'course_id' => $dto->courseId,
            ],
            $dto->toArray(),
        );
    }

    /**
     * Delete a review by user.
     *
     * @param int $userId
     * @param int $courseId
     *
     * @return boolean
     */
    public function deleteReviewByUser(int $userId, int $courseId): bool
    {
        return (bool) CourseReview::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->delete();
    }

    /**
     * Delete a review by admin.
     *
     * @param CourseReview $review
     *
     * @return boolean
     */
    public function deleteReviewByAdmin(CourseReview $review): bool
    {
        return (bool) $review->delete();
    }
}
