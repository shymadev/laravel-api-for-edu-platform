<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Education\MarkLessonCompleteRequest;
use App\Http\Requests\Education\SaveBlockProgressRequest;
use App\Http\Resources\Progress\CourseProgressResource;
use App\Http\Resources\Progress\UserStatisticsResource;
use App\Services\CourseProgressService;
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
     * Construct a new UserProgressController instance.
     *
     * @param \App\Services\CourseProgressService $courseProgressService
     *
     * @return void
     */
    public function __construct(protected readonly CourseProgressService $courseProgressService)
    {
    }

    /**
     * Get a list of lessons completed by the authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completedLessons(): JsonResponse
    {
        try {
            $completedLessons = $this->courseProgressService->completedLessons((int) auth()->id());

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
     * @param \App\Http\Requests\Education\MarkLessonCompleteRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markLessonComplete(MarkLessonCompleteRequest $request): JsonResponse
    {
        $userId = (int) auth()->id();
        $lessonId = (int) $request->validated()['lesson_id'];

        try {
            $marked = $this->courseProgressService->markLessonComplete($userId, $lessonId);

            if (!$marked) {
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
     * @param int $courseId
     *
     * @return \App\Http\Resources\Progress\CourseProgressResource
     */
    public function courseProgress(int $courseId): CourseProgressResource
    {
        try {
            $progress = $this->courseProgressService->courseProgress((int) auth()->id(), $courseId);

            return new CourseProgressResource($progress);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve course progress', ['user_id' => auth()->id(), 'course_id' => $courseId, 'error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve course progress');
        }
    }

    /**
     * Get the user's progress for all courses.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function coursesProgress(): AnonymousResourceCollection
    {
        try {
            $progress = $this->courseProgressService->userCoursesProgress((int) auth()->id());

            return CourseProgressResource::collection($progress);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve courses progress', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve courses progress');
        }
    }

    /**
     * Get completed block indexes for a specific lesson.
     *
     * @param int $lessonId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lessonBlockProgress(int $lessonId): JsonResponse
    {
        try {
            $progress = $this->courseProgressService->lessonBlockProgress((int) auth()->id(), $lessonId);

            return response()->json($progress);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve lesson block progress', ['user_id' => auth()->id(), 'lesson_id' => $lessonId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to retrieve block progress', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Save block progress (upsert). Supports partial state and completion flag.
     *
     * @param \App\Http\Requests\Education\SaveBlockProgressRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveBlockProgress(SaveBlockProgressRequest $request): JsonResponse
    {
        $userId = (int) auth()->id();
        $validated = $request->validated();

        try {
            $this->courseProgressService->saveBlockProgress(
                $userId,
                (int) $validated['lesson_id'],
                (int) $validated['block_index'],
                (string) $validated['block_type'],
                (bool) ($validated['is_completed'] ?? false),
                $validated['block_state'] ?? null,
            );

            return response()->json(['saved' => true], 201);
        } catch (\Exception $e) {
            Log::error('Failed to save block progress', ['user_id' => $userId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to save block progress', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Stop tracking the authenticated user's progress for a course.
     *
     * @param int $courseId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stopCourse(int $courseId): JsonResponse
    {
        $userId = (int) auth()->id();

        try {
            $this->courseProgressService->stopCourse($userId, $courseId);
            Log::info('User stopped course', ['user_id' => $userId, 'course_id' => $courseId]);

            return response()->json(['stopped' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to stop course', ['user_id' => $userId, 'course_id' => $courseId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to stop course'], 500);
        }
    }

    /**
     * Resume tracking the authenticated user's progress for a previously stopped course.
     *
     * @param int $courseId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resumeCourse(int $courseId): JsonResponse
    {
        $userId = (int) auth()->id();

        try {
            $this->courseProgressService->resumeCourse($userId, $courseId);
            Log::info('User resumed course', ['user_id' => $userId, 'course_id' => $courseId]);

            return response()->json(['resumed' => true]);
        } catch (\Exception $e) {
            Log::error('Failed to resume course', ['user_id' => $userId, 'course_id' => $courseId, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Failed to resume course'], 500);
        }
    }

    /**
     * Get overall statistics for the user.
     *
     * @return \App\Http\Resources\Progress\UserStatisticsResource
     */
    public function statistics(): UserStatisticsResource
    {
        try {
            $stats = $this->courseProgressService->userStatistics((int) auth()->id());

            return new UserStatisticsResource($stats);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve user statistics', ['user_id' => auth()->id(), 'error' => $e->getMessage()]);
            abort(500, 'Failed to retrieve user statistics');
        }
    }
}
