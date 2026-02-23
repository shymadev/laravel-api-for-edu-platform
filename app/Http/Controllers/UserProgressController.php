<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Education\MarkLessonCompleteRequest;
use App\Http\Resources\Progress\CourseProgressResource;
use App\Http\Resources\Progress\UserStatisticsResource;
use App\Services\Contracts\Progress\CourseProgressServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller that handles user progress tracking.
 */
class UserProgressController extends Controller
{
    /**
     * Course progress service instance.
     *
     * @var CourseProgressServiceInterface
     */
    protected CourseProgressServiceInterface $progressService;

    /**
     * Construct a new UserProgressController instance.
     *
     * @param CourseProgressServiceInterface $progressService
     */
    public function __construct(CourseProgressServiceInterface $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Get a list of lessons completed by the authenticated user.
     *
     * @return JsonResponse returns the list of completed lessons
     */
    public function completedLessons(): JsonResponse
    {
        try {
            $completedLessons = $this->progressService->completedLessons((int) auth()->id());

            return response()->json($completedLessons);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve completed lessons', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to retrieve completed lessons',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark a lesson as complete for the authenticated user.
     *
     * @param MarkLessonCompleteRequest $request The validated request containing lesson ID
     *
     * @return JsonResponse returns success or already-completed message
     */
    public function markLessonComplete(MarkLessonCompleteRequest $request): JsonResponse
    {
        $userId = (int) auth()->id();
        $lessonId = (int) $request->validated()['lesson_id'];

        try {
            $marked = $this->progressService->markLessonComplete($userId, $lessonId);

            if (! $marked) {
                Log::info('Lesson already completed', ['user_id' => $userId, 'lesson_id' => $lessonId]);

                return response()->json(['message' => 'Lesson already completed', 'alreadyCompleted' => true]);
            }

            Log::info('Lesson marked as complete', ['user_id' => $userId, 'lesson_id' => $lessonId]);

            return response()->json(['message' => 'Lesson marked as complete', 'completed' => true], 201);
        } catch (\Exception $e) {
            Log::error('Failed to mark lesson as complete', ['user_id' => $userId, 'lesson_id' => $lessonId, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Failed to mark lesson as complete',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate the user's progress for a specific course.
     *
     * @param int $courseId The course ID
     *
     * @return CourseProgressResource returns course progress details
     */
    public function courseProgress(int $courseId): CourseProgressResource
    {
        try {
            $progress = $this->progressService->courseProgress((int) auth()->id(), $courseId);

            return new CourseProgressResource($progress);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve course progress', ['user_id' => auth()->id(), 'course_id' => $courseId, 'error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve course progress');
        }
    }

    /**
     * Get the user's progress for all courses.
     *
     * @return AnonymousResourceCollection returns collection of course progress
     */
    public function coursesProgress(): AnonymousResourceCollection
    {
        try {
            $progress = $this->progressService->userCoursesProgress((int) auth()->id());

            return CourseProgressResource::collection($progress);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve courses progress', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve courses progress');
        }
    }

    /**
     * Get overall statistics for the user.
     *
     * @return UserStatisticsResource returns aggregated statistics of the user
     */
    public function statistics(): UserStatisticsResource
    {
        try {
            $stats = $this->progressService->userStatistics((int) auth()->id());

            return new UserStatisticsResource($stats);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user statistics', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve user statistics');
        }
    }
}
