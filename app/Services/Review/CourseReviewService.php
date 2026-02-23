<?php

namespace App\Services\Review;

use App\DTO\Review\CreateReviewDTO;
use App\Models\Education\CourseReview;
use App\Services\Contracts\Review\CourseReviewServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class CourseReviewService implements CourseReviewServiceInterface
{
    public function getReviewsByCourse(int $courseId): Collection
    {
        return CourseReview::with('user.profile')
            ->where('course_id', $courseId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getCourseRating(int $courseId): array
    {
        $reviews = CourseReview::where('course_id', $courseId)->get();
        $count = $reviews->count();

        if ($count === 0) {
            return [
                'average_rating' => 0,
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
    }

    public function createOrUpdateReview(CreateReviewDTO $dto): CourseReview
    {
        return CourseReview::updateOrCreate(
            [
                'user_id' => $dto->userId,
                'course_id' => $dto->courseId,
            ],
            $dto->toArray()
        );
    }

    public function deleteReviewByUser(int $userId, int $courseId): bool
    {
        return (bool) CourseReview::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->delete();
    }

    public function deleteReviewByAdmin(CourseReview $review): bool
    {
        return (bool) $review->delete();
    }
}
