<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\DTO\Review\CreateReviewDTO;
use App\Http\Requests\Review\CreateReviewRequest;
use App\Http\Resources\Review\CourseReviewResource;
use App\Models\Education\CourseReview;
use App\Models\User\UserCompletedLesson;
use App\Services\CourseReviewService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller for managing course reviews.
 */
class CourseReviewController extends Controller
{
    /**
     * Constructs the CourseReviewController.
     *
     * @param \App\Services\CourseReviewService $courseReviewService
     *
     * @return void
     */
    public function __construct(
        protected readonly CourseReviewService $courseReviewService,
    ) {
    }

    /**
     * Retrieves all reviews for a specific course.
     *
     * @param int $courseId
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(int $courseId): AnonymousResourceCollection
    {
        $reviews = $this->courseReviewService->getReviewsByCourse($courseId);

        return CourseReviewResource::collection($reviews);
    }

    /**
     * Retrieves the average rating for a specific course.
     *
     * @param int $courseId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rating(int $courseId): JsonResponse
    {
        $rating = $this->courseReviewService->getCourseRating($courseId);

        return response()->json(['data' => $rating]);
    }

    /**
     * Creates or updates a review for a specific course.
     *
     * @param \App\Http\Requests\Review\CreateReviewRequest $request
     * @param int $courseId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateReviewRequest $request, int $courseId): JsonResponse
    {
        $hasCompletedLesson = UserCompletedLesson::where('user_id', auth()->id())
            ->whereHas('lesson.topic', function (Builder $query) use ($courseId): void {
                $query->where('course_id', $courseId);
            })->exists();

        if (!$hasCompletedLesson) {
            return response()->json([
                'message' => 'You must complete at least one lesson before leaving a review.',
            ], 403);
        }

        try {
            $dto = new CreateReviewDTO(
                userId: auth()->id(),
                courseId: $courseId,
                rating: $request->validated('rating'),
                reviewText: $request->validated('review_text'),
            );

            $review = $this->courseReviewService->createOrUpdateReview($dto);

            Log::channel('db')->info('Course review created or updated', [
                'review_id' => $review->id,
                'course_id' => $courseId,
                'user_id' => auth()->id(),
                'action' => 'course_review_upsert',
            ]);

            return response()->json([
                'message' => 'Review saved successfully',
                'data' => new CourseReviewResource($review),
            ], 201);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to save course review', [
                'course_id' => $courseId,
                'user_id' => auth()->id(),
                'action' => 'course_review_upsert_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to save review',
            ], 500);
        }
    }

    /**
     * Deletes a review for a specific course by the authenticated user.
     *
     * @param int $courseId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $courseId): JsonResponse
    {
        try {
            $this->courseReviewService->deleteReviewByUser(auth()->id(), $courseId);

            Log::channel('db')->info('Course review deleted by user', [
                'course_id' => $courseId,
                'user_id' => auth()->id(),
                'action' => 'course_review_delete_user',
            ]);

            return response()->json(null, 204);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to delete course review by user', [
                'course_id' => $courseId,
                'user_id' => auth()->id(),
                'action' => 'course_review_delete_user_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to delete review',
            ], 500);
        }
    }

    /**
     * Deletes a review for a specific course by an admin.
     *
     * @param \App\Models\Education\CourseReview $review
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyAdmin(CourseReview $review): JsonResponse
    {
        try {
            $this->courseReviewService->deleteReviewByAdmin($review);

            Log::channel('db')->info('Course review deleted by admin', [
                'review_id' => $review->id,
                'course_id' => $review->course_id,
                'admin_id' => auth()->id(),
                'action' => 'course_review_delete_admin',
            ]);

            return response()->json(null, 204);

        } catch (\Throwable $e) {
            Log::channel('db')->error('Failed to delete course review by admin', [
                'review_id' => $review->id,
                'course_id' => $review->course_id,
                'admin_id' => auth()->id(),
                'action' => 'course_review_delete_admin_failed',
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Failed to delete review',
            ], 500);
        }
    }
}
